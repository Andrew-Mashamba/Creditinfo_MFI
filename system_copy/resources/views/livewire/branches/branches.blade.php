<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-blue-900 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Branches Management</h1>
                        <p class="text-gray-600 mt-1">Manage, monitor, and organize branch operations</p>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Branches</p>
                                <p class="text-lg font-semibold text-gray-900">{{ App\Models\BranchesModel::count() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Active Branches</p>
                                <p class="text-lg font-semibold text-gray-900">{{ App\Models\BranchesModel::where('status',"ACTIVE")->count() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Inactive Branches</p>
                                <p class="text-lg font-semibold text-gray-900">{{App\Models\BranchesModel::where('status','!=',"ACTIVE")->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto branches-scroll pb-8">
            <div class="flex gap-6">
                <!-- Enhanced Sidebar -->
                <div class="w-80 shrink-0 bg-white rounded-2xl shadow-lg border border-gray-100">
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
                                placeholder="Search branches..."
                                class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white"
                                aria-label="Search branches"
                            />
                        </div>
                    </div>

                    <!-- Navigation Menu -->
                    <div class="p-4">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Navigation</h3>
                        
                        @php
                            $branch_sections = [
                                [
                                    'id' => 1, 
                                    'label' => 'Branches Overview', 
                                    'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                                    'description' => 'View all branches'
                                ],
                                [
                                    'id' => 2, 
                                    'label' => 'New Branch', 
                                    'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                                    'description' => 'Create new branch'
                                ],
                            ];
                        @endphp

                        <nav class="space-y-2">
                            @foreach ($branch_sections as $section)
                                @php
                                    $isActive = $this->tab_id == $section['id'];
                                @endphp

                                <button
                                    wire:click="showAddBranchModal({{ $section['id'] }})"
                                    class="relative w-full group transition-all duration-200"
                                    aria-label="{{ $section['label'] }}"
                                >
                                    <div class="flex items-center p-3 rounded-xl transition-all duration-200
                                        @if ($isActive) 
                                            bg-blue-900 text-white shadow-lg 
                                        @else 
                                            bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900 
                                        @endif">
                                        
                                        <!-- Loading State -->
                                        <div wire:loading wire:target="showAddBranchModal" class="mr-3">
                                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>

                                        <!-- Icon -->
                                        <div wire:loading.remove wire:target="showAddBranchModal" class="mr-3">
                                            <svg class="w-5 h-5 @if ($isActive) text-white @else text-gray-500 group-hover:text-gray-700 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            @endforeach
                        </nav>
                    </div>
                 
                </div>

                <!-- Enhanced Main Content Area -->
                <div class="flex-1">
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                        <!-- Content Header -->
                        <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900">
                                        @switch($this->tab_id)
                                            @case(1) Branches Overview @break
                                            @case(2) New Branch @break
                                            @default Branches Overview
                                        @endswitch
                                    </h2>
                                    <p class="text-gray-600 mt-1">
                                        @switch($this->tab_id)
                                            @case(1) View and manage all branch locations @break
                                            @case(2) Create and register new branch locations @break
                                            @default View and manage all branch locations
                                        @endswitch
                                    </p>
                                </div>
                                
                                <!-- Breadcrumb -->
                                <nav class="flex" aria-label="Breadcrumb">
                                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                        <li class="inline-flex items-center">
                                            <a href="#" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                                </svg>
                                                Branches
                                            </a>
                                        </li>
                                        <li>
                                            <div class="flex items-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                                                    @switch($this->tab_id)
                                                        @case(1) Overview @break
                                                        @case(2) New Branch @break
                                                        @default Overview
                                                    @endswitch
                                                </span>
                                            </div>
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <!-- Main Content -->
                        <div class="p-8 branches-content-scroll">
                            <!-- Dynamic Content -->
                            <div wire:loading.remove wire:target="showAddBranchModal" class="min-h-[400px]">
                                @switch($this->tab_id)
                                    @case(1)
                                        <!-- Branches Overview -->
                                        @if($this->viewBranchDetails)
                                            @include('livewire.branches.branch-details')
                                        @else
                                            <livewire:branches.branches-table />
                                        @endif
                                        @break
                                        
                                    @case(2)
                                        <!-- New Branch Form -->
                                        <div class="bg-white border border-gray-200 rounded-xl p-6">
                                            <div class="flex items-center justify-between mb-6">
                                                <div>
                                                    <h3 class="text-xl font-bold text-gray-900">Create New Branch</h3>
                                                    <p class="text-gray-600 mt-1">Fill in the details below to register a new branch location.</p>
                                                </div>
                                                <button 
                                                    wire:click="resetFormAndGenerateBranchNumber" 
                                                    class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium"
                                                >
                                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                    </svg>
                                                    Reset Form
                                                </button>
                                            </div>

                                            {{-- Success Message --}}
                                            @if (session()->has('message'))
                                                @if (session('alert-class') == 'alert-success')
                                                    <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                                                        <div class="flex items-center">
                                                            <svg class="w-5 h-5 text-emerald-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            <div>
                                                                <p class="font-semibold text-emerald-800">Success!</p>
                                                                <p class="text-sm text-emerald-700">{{ session('message') }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif

                                            <!-- Step-by-Step Form Layout -->
                                            <div class="space-y-8">
                                                
                                                <!-- Step 1: Basic Information -->
                                                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                                                    <div class="flex items-center mb-4">
                                                        <span class="flex items-center justify-center w-8 h-8 bg-blue-900 text-white rounded-full text-sm font-bold mr-3">1</span>
                                                        <h4 class="text-lg font-semibold text-gray-900">Basic Information</h4>
                                                    </div>
                                                    
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <div>
                                                            <label for="branch_number" class="block text-sm font-medium text-gray-700 mb-2">Branch Number</label>
                                                            <input 
                                                                id="branch_number" 
                                                                type="text" 
                                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 focus:outline-none" 
                                                                wire:model="branch_number" 
                                                                disabled
                                                            />
                                                            @error('branch_number') 
                                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    {{ $message }}
                                                                </p>
                                                            @enderror
                                                        </div>

                                                        <div>
                                                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Branch Name <span class="text-red-500">*</span></label>
                                                            <input 
                                                                id="name" 
                                                                type="text" 
                                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                                wire:model="name" 
                                                                placeholder="Enter branch name"
                                                            />
                                                            @error('name') 
                                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    {{ $message }}
                                                                </p>
                                                            @enderror
                                                        </div>

                                                        <div>
                                                            <label for="region" class="block text-sm font-medium text-gray-700 mb-2">Region <span class="text-red-500">*</span></label>
                                                            <input 
                                                                id="region" 
                                                                type="text" 
                                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                                wire:model="region" 
                                                                placeholder="Enter region"
                                                            />
                                                            @error('region') 
                                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    {{ $message }}
                                                                </p>
                                                            @enderror
                                                        </div>

                                                        <div>
                                                            <label for="wilaya" class="block text-sm font-medium text-gray-700 mb-2">Wilaya <span class="text-red-500">*</span></label>
                                                            <input 
                                                                id="wilaya" 
                                                                type="text" 
                                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                                wire:model="wilaya" 
                                                                placeholder="Enter wilaya"
                                                            />
                                                            @error('wilaya') 
                                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    {{ $message }}
                                                                </p>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Step 2: Contact Information -->
                                                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                                                    <div class="flex items-center mb-4">
                                                        <span class="flex items-center justify-center w-8 h-8 bg-blue-900 text-white rounded-full text-sm font-bold mr-3">2</span>
                                                        <h4 class="text-lg font-semibold text-gray-900">Contact Information</h4>
                                                    </div>
                                                    
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <div>
                                                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address <span class="text-red-500">*</span></label>
                                                            <input 
                                                                id="email" 
                                                                type="email" 
                                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                                wire:model="email" 
                                                                placeholder="branch@company.com"
                                                            />
                                                            @error('email') 
                                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    {{ $message }}
                                                                </p>
                                                            @enderror
                                                        </div>

                                                        <div>
                                                            <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number <span class="text-red-500">*</span></label>
                                                            <input 
                                                                id="phone_number" 
                                                                type="tel" 
                                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                                wire:model="phone_number" 
                                                                placeholder="+1 (555) 123-4567"
                                                            />
                                                            @error('phone_number') 
                                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    {{ $message }}
                                                                </p>
                                                            @enderror
                                                        </div>

                                                        <div class="md:col-span-2">
                                                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Physical Address <span class="text-red-500">*</span></label>
                                                            <textarea 
                                                                id="address" 
                                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                                wire:model="address" 
                                                                rows="3"
                                                                placeholder="Enter complete address including street, city, state"
                                                            ></textarea>
                                                            @error('address') 
                                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    {{ $message }}
                                                                </p>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Step 3: Branch Details -->
                                                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                                                    <div class="flex items-center mb-4">
                                                        <span class="flex items-center justify-center w-8 h-8 bg-blue-900 text-white rounded-full text-sm font-bold mr-3">3</span>
                                                        <h4 class="text-lg font-semibold text-gray-900">Branch Details</h4>
                                                    </div>
                                                    
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <div>
                                                            <label for="branch_type" class="block text-sm font-medium text-gray-700 mb-2">Branch Type <span class="text-red-500">*</span></label>
                                                            <select 
                                                                id="branch_type" 
                                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                                wire:model="branch_type"
                                                            >
                                                                <option value="">Select Branch Type</option>
                                                                <option value="MAIN">Main Branch</option>
                                                                <option value="SUB">Sub Branch</option>
                                                                <option value="MOBILE">Mobile Branch</option>
                                                            </select>
                                                            @error('branch_type') 
                                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    {{ $message }}
                                                                </p>
                                                            @enderror
                                                        </div>

                                                        <div>
                                                            <label for="opening_date" class="block text-sm font-medium text-gray-700 mb-2">Opening Date <span class="text-red-500">*</span></label>
                                                            <input 
                                                                id="opening_date" 
                                                                type="date" 
                                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                                wire:model="opening_date" 
                                                            />
                                                            @error('opening_date') 
                                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    {{ $message }}
                                                                </p>
                                                            @enderror
                                                        </div>

                                                        <div class="md:col-span-2">
                                                            <label for="branch_manager" class="block text-sm font-medium text-gray-700 mb-2">Branch Manager <span class="text-red-500">*</span></label>
                                                            <select 
                                                                id="branch_manager" 
                                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                                wire:model="branch_manager"
                                                            >
                                                                <option value="">Select Branch Manager</option>
                                                                @foreach($branchManagers as $user)
                                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('branch_manager') 
                                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    {{ $message }}
                                                                </p>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Step 4: Operating Hours & Services -->
                                                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                                                    <div class="flex items-center mb-4">
                                                        <span class="flex items-center justify-center w-8 h-8 bg-blue-900 text-white rounded-full text-sm font-bold mr-3">4</span>
                                                        <h4 class="text-lg font-semibold text-gray-900">Operating Hours & Services</h4>
                                                    </div>
                                                    
                                                    <div class="space-y-6">
                                                        <!-- Operating Hours -->
                                                        <div>
                                                            <h5 class="text-sm font-medium text-gray-700 mb-3">Operating Hours</h5>
                                                            <div class="grid grid-cols-2 gap-4">
                                                                <div>
                                                                    <label for="opening_time" class="block text-xs font-medium text-gray-600 mb-2">Opening Time</label>
                                                                    <input 
                                                                        id="opening_time" 
                                                                        type="time" 
                                                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                                        wire:model="opening_time" 
                                                                    />
                                                                </div>
                                                                <div>
                                                                    <label for="closing_time" class="block text-xs font-medium text-gray-600 mb-2">Closing Time</label>
                                                                    <input 
                                                                        id="closing_time" 
                                                                        type="time" 
                                                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                                        wire:model="closing_time" 
                                                                    />
                                                                </div>
                                                            </div>
                                                            @error('operating_hours') 
                                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    {{ $message }}
                                                                </p>
                                                            @enderror
                                                        </div>

                                                        <!-- CIT Provider -->
                                                        <div>
                                                            <h5 class="text-sm font-medium text-gray-700 mb-3">Cash-in-Transit (CIT) Provider <span class="text-red-500">*</span></h5>
                                                            <select 
                                                                id="cit_provider_id" 
                                                                wire:model="cit_provider_id" 
                                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                                            >
                                                                <option value="">Choose CIT Provider...</option>
                                                                @foreach($citProviders as $provider)
                                                                    <option value="{{ $provider->id }}">
                                                                        {{ $provider->name }} - {{ $provider->company_code }}
                                                                        ({{ number_format($provider->service_fee_percentage, 2) }}% fee)
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('cit_provider_id') 
                                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    {{ $message }}
                                                                </p>
                                                            @enderror
                                                        </div>

                                                        <!-- Automatic Account Creation Info -->
                                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                                            <div class="flex items-start space-x-3">
                                                                <div class="p-2 bg-blue-100 rounded-lg">
                                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                    </svg>
                                                                </div>
                                                                <div class="flex-1">
                                                                    <h4 class="text-sm font-semibold text-blue-900 mb-2">Automatic Account Setup</h4>
                                                                    <p class="text-sm text-blue-700 mb-3">Creating this branch will automatically set up the following accounts:</p>
                                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                                        <div class="bg-white rounded-lg p-3 border border-blue-200">
                                                                            <div class="flex items-center space-x-2">
                                                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                                                </svg>
                                                                                <span class="text-sm font-medium text-blue-900">Vault Account</span>
                                                                            </div>
                                                                            <p class="text-xs text-blue-600 mt-1">Secure cash storage</p>
                                                                        </div>
                                                                        <div class="bg-white rounded-lg p-3 border border-blue-200">
                                                                            <div class="flex items-center space-x-2">
                                                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                                                </svg>
                                                                                <span class="text-sm font-medium text-blue-900">Till Account</span>
                                                                            </div>
                                                                            <p class="text-xs text-blue-600 mt-1">Daily operations</p>
                                                                        </div>
                                                                        <div class="bg-white rounded-lg p-3 border border-blue-200">
                                                                            <div class="flex items-center space-x-2">
                                                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                                                </svg>
                                                                                <span class="text-sm font-medium text-blue-900">Petty Cash</span>
                                                                            </div>
                                                                            <p class="text-xs text-blue-600 mt-1">Small expenses</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Submit Button -->
                                                <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center text-sm text-gray-500">
                                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            <span class="text-red-500">*</span> Required fields
                                                        </div>
                                                        <div class="flex items-center space-x-3">
                                                            <button 
                                                                wire:click="resetFormAndGenerateBranchNumber" 
                                                                class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200"
                                                            >
                                                                Clear Form
                                                            </button>
                                                            <button 
                                                                wire:click="addBranch" 
                                                                wire:loading.attr="disabled" 
                                                                class="px-8 py-2.5 bg-blue-900 text-white rounded-lg hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
                                                            >
                                                                <span wire:loading wire:target="addBranch">
                                                                    <div class="flex items-center">
                                                                    <svg class="w-4 h-4 animate-spin mr-2" fill="none" viewBox="0 0 24 24">
                                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                                    </svg>
                                                                    Creating...
                                                                    </div>
                                                                </span>
                                                                <span wire:loading.remove wire:target="addBranch">
                                                                    <div class="flex items-center">
                                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                                    </svg>
                                                                    Create Branch
                                                                    </div>
                                                                </span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @break
                                        
                                    @default
                                        <!-- Default Branches View -->
                                        @if($this->viewBranchDetails)
                                            @include('livewire.branches.branch-details')
                                        @else
                                            <livewire:branches.branches-table />
                                        @endif
                                @endswitch
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Branch Modal -->
    @include('livewire.branches.edit-branch')

    <!-- Delete Branch Confirmation Modal -->
    <div class="w-full container-fluid">
        @if($this->showDeleteBranch)
            <div class="fixed z-10 inset-0 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                    <div class="fixed inset-0 transition-opacity">
                        <div class="absolute inset-0 bg-black opacity-50"></div>
                    </div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div>
                            @if (session()->has('message'))
                                @if (session('alert-class') == 'alert-success')
                                    <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8" role="alert">
                                        <div class="flex">
                                            <div class="py-1"><svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                                            <div>
                                                <p class="font-bold">The process is completed</p>
                                                <p class="text-sm">{{ session('message') }} </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if (session('alert-class') == 'alert-warning')
                                    <div class="bg-yellow-100 border-t-4 border-yellow-500 rounded-b text-yellow-900 px-4 py-3 shadow-md mb-8" role="alert">
                                        <div class="flex">
                                            <div class="py-1"><svg class="fill-current h-6 w-6 text-yellow-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                                            <div>
                                                <p class="font-bold">Error</p>
                                                <p class="text-sm">{{ session('message') }} </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            <div class="flex w-full">
                                @if($this->branchSelected)
                                    <div class="w-full p-4">
                                        <p class="block mb-1 text-sm capitalize text-gray-400">BRANCH SELECTED</p>
                                        <div class="flex items-center mb-2 text-sm spacing-sm text-gray-600 mt-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <p>{{ App\Models\BranchesModel::where('id', $this->branchSelected)->value('name') }}</p>
                                        </div>

                                        <div class="mt-4 w-full">
                                            <p for="branchSelected" class="block mb-1 text-sm capitalize text-gray-400">SELECT ACTION</p>
                                            <div class="flex gap-4 items-center text-center">
                                                <label class="inline-flex items-center">
                                                    <input wire:model="permission" name="setSubMenuPermission" type="radio" value="INACTIVE" checked>
                                                    <span class="ml-2">Block</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input wire:model="permission" name="setSubMenuPermission" type="radio" value="ACTIVE" />
                                                    <span class="ml-2">Activate</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input wire:model="permission" name="setSubMenuPermission" type="radio" value="DELETED" />
                                                    <span class="ml-2">Delete</span>
                                                </label>
                                            </div>
                                            @error('permission') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="mt-4">
                                            <p class="text-sm text-gray-600">
                                                @if($permission === 'INACTIVE')
                                                    This will block the branch from being used in the system.
                                                @elseif($permission === 'ACTIVE')
                                                    This will reactivate the branch for use in the system.
                                                @elseif($permission === 'DELETED')
                                                    <span class="text-red-600 font-semibold">Warning:</span> This will permanently delete the branch from the system. This action cannot be undone.
                                                @endif
                                            </p>
                                        </div>

                                        <div class="mt-4">
                                            <p for="password" class="block mb-1 text-sm capitalize text-gray-400">ENTER PASSWORD TO CONFIRM</p>
                                            <input wire:model="password" id="current_password" type="password" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" required />
                                            @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center bg-gray-200 justify-end py-3 sm:px-6 sm:rounded-bl-lg sm:rounded-br-lg">
                            <button type="button" wire:click="$toggle('showDeleteBranch')" class="mr-4 inline-flex justify-center px-4 py-2 text-sm font-medium border border-transparent rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 bg-white">
                                Cancel
                            </button>
                            <button wire:click="confirmPassword" wire:loading.attr="disabled" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-400 border border-transparent rounded-md focus-visible:ring-2 focus-visible:ring-offset-2">
                                <span wire:loading wire:target="confirmPassword">Loading...</span>
                                <span wire:loading.remove wire:target="confirmPassword">Proceed</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Add Branch Modal -->
    <div class="w-full container-fluid">
        @if($this->showAddBranch)
            <div class="fixed z-50 inset-0 overflow-y-auto">
                <!-- Enhanced backdrop with blur effect -->
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                    <div class="fixed inset-0 transition-opacity">
                        <div class="absolute inset-0 bg-gray-500/75 transition-opacity"></div>
                    </div>
                    
                    <!-- Modal Container - More compact width -->
                    <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle w-full max-w-3xl">
                        
                        <!-- Modal Header -->
                        <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                                        <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900">Create New Branch</h3>
                                        <p class="text-gray-900 text-sm">Register a new branch location</p>
                                    </div>
                                </div>
                                <button 
                                    wire:click="closeShowAddBranch" 
                                    class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors duration-200"
                                >
                                    <svg class="w-5 h-5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Success Message -->
                        @if (session()->has('message'))
                            @if (session('alert-class') == 'alert-success')
                                <div class="mx-6 mt-4 bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-emerald-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div>
                                            <p class="font-semibold text-emerald-800">Success!</p>
                                            <p class="text-sm text-emerald-700">{{ session('message') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <!-- Modal Body with Enhanced Scrolling -->
                        <div class="px-6 py-6 max-h-[calc(100vh-200px)] overflow-y-auto custom-scrollbar">
                            
                            <!-- Step-by-Step Form Layout -->
                            <div class="space-y-8">
                                
                                <!-- Step 1: Basic Information -->
                                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                                    <div class="flex items-center mb-4">
                                        <span class="flex items-center justify-center w-8 h-8 bg-blue-900 text-white rounded-full text-sm font-bold mr-3">1</span>
                                        <h4 class="text-lg font-semibold text-gray-900">Basic Information</h4>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="branch_number" class="block text-sm font-medium text-gray-700 mb-2">Branch Number</label>
                                            <input 
                                                id="branch_number" 
                                                type="text" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 focus:outline-none" 
                                                wire:model="branch_number" 
                                                disabled
                                            />
                                            @error('branch_number') 
                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Branch Name <span class="text-red-500">*</span></label>
                                            <input 
                                                id="name" 
                                                type="text" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                wire:model="name" 
                                                placeholder="Enter branch name"
                                            />
                                            @error('name') 
                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="region" class="block text-sm font-medium text-gray-700 mb-2">Region <span class="text-red-500">*</span></label>
                                            <input 
                                                id="region" 
                                                type="text" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                wire:model="region" 
                                                placeholder="Enter region"
                                            />
                                            @error('region') 
                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="wilaya" class="block text-sm font-medium text-gray-700 mb-2">Wilaya <span class="text-red-500">*</span></label>
                                            <input 
                                                id="wilaya" 
                                                type="text" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                wire:model="wilaya" 
                                                placeholder="Enter wilaya"
                                            />
                                            @error('wilaya') 
                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 2: Contact Information -->
                                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                                    <div class="flex items-center mb-4">
                                        <span class="flex items-center justify-center w-8 h-8 bg-blue-900 text-white rounded-full text-sm font-bold mr-3">2</span>
                                        <h4 class="text-lg font-semibold text-gray-900">Contact Information</h4>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address <span class="text-red-500">*</span></label>
                                            <input 
                                                id="email" 
                                                type="email" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                wire:model="email" 
                                                placeholder="branch@company.com"
                                            />
                                            @error('email') 
                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number <span class="text-red-500">*</span></label>
                                            <input 
                                                id="phone_number" 
                                                type="tel" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                wire:model="phone_number" 
                                                placeholder="+1 (555) 123-4567"
                                            />
                                            @error('phone_number') 
                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <div class="md:col-span-2">
                                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Physical Address <span class="text-red-500">*</span></label>
                                            <textarea 
                                                id="address" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                wire:model="address" 
                                                rows="3"
                                                placeholder="Enter complete address including street, city, state"
                                            ></textarea>
                                            @error('address') 
                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 3: Branch Details -->
                                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                                    <div class="flex items-center mb-4">
                                        <span class="flex items-center justify-center w-8 h-8 bg-blue-900 text-white rounded-full text-sm font-bold mr-3">3</span>
                                        <h4 class="text-lg font-semibold text-gray-900">Branch Details</h4>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="branch_type" class="block text-sm font-medium text-gray-700 mb-2">Branch Type <span class="text-red-500">*</span></label>
                                            <select 
                                                id="branch_type" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                wire:model="branch_type"
                                            >
                                                <option value="">Select Branch Type</option>
                                                <option value="MAIN">Main Branch</option>
                                                <option value="SUB">Sub Branch</option>
                                                <option value="MOBILE">Mobile Branch</option>
                                            </select>
                                            @error('branch_type') 
                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="opening_date" class="block text-sm font-medium text-gray-700 mb-2">Opening Date <span class="text-red-500">*</span></label>
                                            <input 
                                                id="opening_date" 
                                                type="date" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                wire:model="opening_date" 
                                            />
                                            @error('opening_date') 
                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <div class="md:col-span-2">
                                            <label for="branch_manager" class="block text-sm font-medium text-gray-700 mb-2">Branch Manager <span class="text-red-500">*</span></label>
                                            <select 
                                                id="branch_manager" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                wire:model="branch_manager"
                                            >
                                                <option value="">Select Branch Manager</option>
                                                @foreach($branchManagers as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('branch_manager') 
                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 4: Operating Hours & Services -->
                                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                                    <div class="flex items-center mb-4">
                                        <span class="flex items-center justify-center w-8 h-8 bg-blue-900 text-white rounded-full text-sm font-bold mr-3">4</span>
                                        <h4 class="text-lg font-semibold text-gray-900">Operating Hours & Services</h4>
                                    </div>
                                    
                                    <div class="space-y-6">
                                        <!-- Operating Hours -->
                                        <div>
                                            <h5 class="text-sm font-medium text-gray-700 mb-3">Operating Hours</h5>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label for="opening_time" class="block text-xs font-medium text-gray-600 mb-2">Opening Time</label>
                                                    <input 
                                                        id="opening_time" 
                                                        type="time" 
                                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                        wire:model="opening_time" 
                                                    />
                                                </div>
                                                <div>
                                                    <label for="closing_time" class="block text-xs font-medium text-gray-600 mb-2">Closing Time</label>
                                                    <input 
                                                        id="closing_time" 
                                                        type="time" 
                                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                                        wire:model="closing_time" 
                                                    />
                                                </div>
                                            </div>
                                            @error('operating_hours') 
                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <!-- CIT Provider -->
                                        <div>
                                            <h5 class="text-sm font-medium text-gray-700 mb-3">Cash-in-Transit (CIT) Provider <span class="text-red-500">*</span></h5>
                                            <select 
                                                id="cit_provider_id" 
                                                wire:model="cit_provider_id" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            >
                                                <option value="">Choose CIT Provider...</option>
                                                @foreach($citProviders as $provider)
                                                    <option value="{{ $provider->id }}">
                                                        {{ $provider->name }} - {{ $provider->company_code }}
                                                        ({{ number_format($provider->service_fee_percentage, 2) }}% fee)
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('cit_provider_id') 
                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <!-- Automatic Account Creation Info -->
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                            <div class="flex items-start space-x-3">
                                                <div class="p-2 bg-blue-100 rounded-lg">
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="text-sm font-semibold text-blue-900 mb-2">Automatic Account Setup</h4>
                                                    <p class="text-sm text-blue-700 mb-3">Creating this branch will automatically set up the following accounts:</p>
                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                        <div class="bg-white rounded-lg p-3 border border-blue-200">
                                                            <div class="flex items-center space-x-2">
                                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                                </svg>
                                                                <span class="text-sm font-medium text-blue-900">Vault Account</span>
                                                            </div>
                                                            <p class="text-xs text-blue-600 mt-1">Secure cash storage</p>
                                                        </div>
                                                        <div class="bg-white rounded-lg p-3 border border-blue-200">
                                                            <div class="flex items-center space-x-2">
                                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                                </svg>
                                                                <span class="text-sm font-medium text-blue-900">Till Account</span>
                                                            </div>
                                                            <p class="text-xs text-blue-600 mt-1">Daily operations</p>
                                                        </div>
                                                        <div class="bg-white rounded-lg p-3 border border-blue-200">
                                                            <div class="flex items-center space-x-2">
                                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                                </svg>
                                                                <span class="text-sm font-medium text-blue-900">Petty Cash</span>
                                                            </div>
                                                            <p class="text-xs text-blue-600 mt-1">Small expenses</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                            <div class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-red-500">*</span> Required fields
                            </div>
                            <div class="flex items-center space-x-3">
                                <button 
                                    type="button" 
                                    wire:click="closeShowAddBranch" 
                                    class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200"
                                >
                                    Cancel
                                </button>
                                <button 
                                    wire:click="addBranch" 
                                    wire:loading.attr="disabled" 
                                    class="px-8 py-2.5 bg-blue-900 text-white rounded-lg hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
                                >
                                    <span wire:loading wire:target="addBranch">
                                        <svg class="w-4 h-4 animate-spin mr-2" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Creating...
                                    </span>
                                    <span wire:loading.remove wire:target="addBranch">
                                        <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Create Branch
                                        </div>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Enhanced Custom Scrollbar Styles -->
    <style nonce="{{ csp_nonce() }}">
        /* Custom scrollbar for the modal */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        /* For Firefox */
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }

        /* Custom red vertical scrollbar for branches main container */
        .branches-scroll::-webkit-scrollbar {
            width: 2px;
        }
        .branches-scroll::-webkit-scrollbar-thumb {
            background: #e3342f;
            border-radius: 2px;
        }
        .branches-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        /* For Firefox */
        .branches-scroll {
            scrollbar-width: thin;
            scrollbar-color: #e3342f transparent;
        }

        /* Custom red vertical scrollbar for branches content area */
        .branches-content-scroll::-webkit-scrollbar {
            width: 2px;
        }
        .branches-content-scroll::-webkit-scrollbar-thumb {
            background: #e3342f;
            border-radius: 2px;
        }
        .branches-content-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        /* For Firefox */
        .branches-content-scroll {
            scrollbar-width: thin;
            scrollbar-color: #e3342f transparent;
        }
    </style>
</div>
