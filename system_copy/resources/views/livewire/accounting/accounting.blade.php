<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        {{-- Header Section --}}
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-blue-900 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Accounting Management</h1>
                        <p class="text-gray-600 mt-1">Comprehensive financial management and reporting portal</p>
                    </div>
                </div>
                
                {{-- Quick Stats --}}
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Institution Accounts</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $totalInstitutionAccounts }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Member Accounts</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $totalMemberAccounts }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Pending Activities</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $pendingActivities }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-6">
    {{-- Enhanced Sidebar --}}
            <aside class="w-80 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden flex-shrink-0">
        <div class="flex flex-col h-full">
            {{-- Header --}}
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Accounting</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Management Portal</p>
                    </div>
                </div>
            </div>

            {{-- Search Bar --}}
            <div class="p-6 border-b border-gray-100">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        wire:model.debounce.300ms="menuSearch"
                        placeholder="Search accounting modules, reports, or functions..."
                        class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white"
                        aria-label="Search accounting modules"
                    />
                    @if($menuSearch)
                        <button 
                            wire:click="$set('menuSearch', '')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors duration-200"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Navigation Menu --}}
            <div class="p-4">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Navigation</h3>
                    @php
                        $menuIcons = [
                            1 => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                            37 => 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
                            2 => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                            3 => 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
                            4 => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                            6 => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                            12 => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                            10 => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                            40 => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                            41 => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                            42 => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                            50 => 'M9 17v1a1 1 0 001 1h1a1 1 0 001-1v-1m3 0v1a1 1 0 001 1h1a1 1 0 001-1v-1m-6-2V9a2 2 0 012-2h2a2 2 0 012 2v6M5 11h14a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm4-7h6a2 2 0 012 2v2H7V6a2 2 0 012-2z',
                            28 => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                            8 => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                            5 => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                            9 => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                            13 => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                            14 => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                            15 => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                            16 => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                            17 => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                            18 => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                            30 => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                            19 => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                            20 => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                            21 => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                            22 => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                            23 => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
                            24 => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                            25 => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                            49 => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                            26 => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                            27 => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                            29 => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
                            31 => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                            32 => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                            33 => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                            34 => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                            35 => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                            36 => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                            38 => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                            39 => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                        43 => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                        44 => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
                        45 => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                        46 => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                        47 => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                        48 => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                        ];
                    @endphp

                    @foreach($menuCategories as $category => $menuIds)
                        @php
                            $filteredItems = collect($menuItems)->whereIn('id', $menuIds);
                            if (isset($menuSearch) && $menuSearch) {
                                $filteredItems = $filteredItems->filter(function($item) use ($menuSearch) {
                                    return str_contains(strtolower($item['label']), strtolower($menuSearch));
                                });
                            }
                        @endphp
                        
                        @if($filteredItems->count() > 0)
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {{ $category }}
                                    </h3>
                                    <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">
                                        {{ $filteredItems->count() }}
                                    </span>
                                </div>
                                
                                <div class="space-y-1">
                                    @foreach($filteredItems as $menuItem)
                                        @php
                                            // Check permissions for each menu item
                                            $showMenuItem = true;
                                            
                                            // Core accounting items - require view permission
                                            if (in_array($menuItem['id'], [37, 47, 48, 3, 4, 6])) {
                                                $showMenuItem = $permissions['canView'] ?? false;
                                            }
                                            
                                            // Financial statements - require reports permission
                                            if (in_array($menuItem['id'], [12, 50, 5, 8, 28, 40, 41, 42])) {
                                                $showMenuItem = ($permissions['canViewReports'] ?? false) || ($permissions['canView'] ?? false);
                                            }
                                            
                                            // Asset management - require edit permission
                                            if (in_array($menuItem['id'], [20, 32, 16])) {
                                                $showMenuItem = ($permissions['canManageAssets'] ?? false) || ($permissions['canEdit'] ?? false);
                                            }
                                            
                                            // Risk management - require special permission
                                            if (in_array($menuItem['id'], [17])) {
                                                $showMenuItem = ($permissions['canManageRisk'] ?? false) || ($permissions['canEdit'] ?? false);
                                            }
                                            
                                            // Operations - require edit permission
                                            if (in_array($menuItem['id'], [18, 30, 21, 24, 25, 49, 26, 27, 29, 36, 19, 22])) {
                                                $showMenuItem = $permissions['canEdit'] ?? false;
                                            }
                                            
                                            // Members - require view permission
                                            if ($menuItem['id'] == 9) {
                                                $showMenuItem = $permissions['canView'] ?? false;
                                            }
                                        @endphp
                                        
                                        @if($showMenuItem)
                                        <button 
                                            wire:click="menuItemClicked({{ $menuItem['id'] }})"
                                            class="group w-full flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 relative overflow-hidden
                                                {{ $this->tab_id == $menuItem['id'] 
                                                    ? 'bg-blue-900 text-white shadow-lg shadow-blue-600/25' 
                                                    : 'text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400' }} 
                                                focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                            title="{{ $menuItem['label'] }}"
                                        >
                                            {{-- Active indicator --}}
                                            @if($this->tab_id == $menuItem['id'])
                                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-white rounded-r-full"></div>
                                            @endif
                                            
                                            {{-- Icon --}}
                                            <div class="flex-shrink-0 mr-3">
                                                <div class="w-5 h-5 flex items-center justify-center">
                                                    @if(isset($menuIcons[$menuItem['id']]))
                                                        <svg class="w-4 h-4 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $menuIcons[$menuItem['id']] }}" />
                                                        </svg>
                                                    @else
                                                        <svg class="w-4 h-4 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            {{-- Label --}}
                                            <span class="flex-1 text-left truncate font-medium text-sm">
                                                {{ $menuItem['label'] }}
                                            </span>
                                            
                                            {{-- Arrow indicator for active item --}}
                                            @if($this->tab_id == $menuItem['id'])
                                                <div class="flex-shrink-0 ml-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </div>
                                            @endif
                                            
                                            {{-- Hover effect overlay --}}
                                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                        </button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                    
                    {{-- No results message --}}
                    @if(isset($menuSearch) && $menuSearch && collect($menuItems)->filter(function($item) use ($menuSearch) { return str_contains(strtolower($item['label']), strtolower($menuSearch)); })->count() == 0)
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">No menu items found</p>
                            <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">Try a different search term</p>
                        </div>
                    @endif
            </div>

            {{-- Footer --}}
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>Total Items: {{ count($menuItems) }}</span>
                    <span>{{ now()->format('M j, Y') }}</span>
                </div>
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <main class="flex-1">


        {{-- Enhanced Main Content Area --}}
        <div class="flex-1">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                {{-- Content Header --}}
                <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">
                                @php
                                    $currentMenuItem = collect($menuItems)->firstWhere('id', $this->tab_id);
                                    $currentMenuLabel = $currentMenuItem['label'] ?? 'Dashboard';
                                @endphp
                                {{ $currentMenuLabel }}
                            </h2>
                            <p class="text-gray-600 mt-1">
                                @switch($this->tab_id)
                                    @case(1) Monitor chart of accounts and account structure @break
                                    @case(2) Create manual journal entries and postings @break
                                    @case(3) Manage external bank and financial accounts @break
                                    @case(4) Process loan disbursements and approvals @break
                                    @case(5) Generate trial balance reports @break
                                    @case(6) Configure standing instructions and automated transfers @break
                                    @case(7) View balance sheet and financial position @break
                                    @case(8) Analyze cash flow statements and liquidity @break
                                    @case(9) Manage member account details and clearances @break
                                    @case(10) Track income and expenditure flows @break
                                    @case(11) Generate profit and loss statements @break
                                    @case(12) Review general ledger statements @break
                                    @case(13) Manage organizational expenses @break
                                    @case(14) Handle petty cash transactions @break
                                    @case(16) Manage intangible assets and amortization @break
                                    @case(17) Configure loan loss reserves and provisions @break
                                    @case(18) Track accounts receivable and collections @break
                                    @case(20) Manage property, plant, and equipment @break
                                    @case(21) Handle insurance policies and claims @break
                                    @case(37) Review detailed ledger accounts @break
                                    @case(43) Manage approval workflows and processes @break
                                    @case(44) Process internal fund transfers between accounts @break
                                    @case(45) Record accounting adjustments and corrections @break
                                    @case(46) Manage till and cash transactions @break
                                    @case(47) View and manage all ledger accounts @break
                                    @case(48) Record manual journal entries and double-entry transactions @break
                                    @case(49) Track interest receivable from loans and investments @break
                                    @default Comprehensive accounting management portal
                                @endswitch
                            </p>
                        </div>
                        
                        {{-- Breadcrumb --}}
                        <nav class="flex" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                <li class="inline-flex items-center">
                                    <a href="#" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                        </svg>
                                        Accounting
                                    </a>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                                            {{ Str::limit($currentMenuLabel, 20) }}
                                        </span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>

                {{-- Main Content --}}
                <div class="p-8">
        {{-- Dynamic Content --}}
                    <div wire:loading.remove wire:target="menuItemClicked" class="min-h-[400px]">
                    @if($this->tab_id == 1)
                        <livewire:accounting.chart-of-accounts/>
                    @elseif($this->tab_id == 2)
                        <livewire:accounting.manual-posting/>
                    @elseif($this->tab_id == 13)
                        <livewire:accounting.expenses />
                    @elseif($this->tab_id == 3)
                        <livewire:accounting.external-accounts/>
                    @elseif($this->tab_id == 4)
                        <livewire:accounting.loans-disbursement/>
                    @elseif($this->tab_id == 5)
                        <livewire:accounting.trial-balance/>
                    @elseif($this->tab_id == 6)
                        <livewire:accounting.standing-instruction/>
                    @elseif($this->tab_id == 7)
                        <livewire:accounting.balance-sheet/>
                    @elseif($this->tab_id == 8)
                        <livewire:accounting.statement-of-cash-flows />
                    @elseif($this->tab_id == 9)
                        @if($this->viewMemberDetails)
                            <livewire:accounting.member-clearance/>
                        @else
                            <livewire:accounting.member-table/>
                        @endif
                    @elseif($this->tab_id == 10)
                        <livewire:accounting.income-and-expenditure/>
                    @elseif($this->tab_id == 11)
                        <livewire:accounting.profit-and-loss-statement/>
                    @elseif($this->tab_id == 12)
                        <livewire:accounting.g-l-statement/>
                    @elseif($this->tab_id == 14)
                        <livewire:accounting.petty-cash />
                    @elseif($this->tab_id == 16)
                        <livewire:accounting.intangible-assets />
                    @elseif($this->tab_id == 17)
                        <livewire:accounting.loan-loss-reserve-manager />
                    @elseif($this->tab_id == 18)
                        <livewire:accounting.trade-and-other-receivables />
                    @elseif($this->tab_id == 20)
                        <livewire:accounting.ppe-management />
                    @elseif($this->tab_id == 21)
                        <livewire:accounting.other-income />
                    @elseif($this->tab_id == 19)
                        <livewire:accounting.insurance />
                    @elseif($this->tab_id == 37)
                        <livewire:accounting.ledger-accounts />
                    @elseif($this->tab_id == 47)
                        <livewire:accounting.list-of-all-accounts />
                    @elseif($this->tab_id == 48)
                        <livewire:accounting.journal-entry />
                    @elseif($this->tab_id == 25)
                        <livewire:accounting.interest-payable />
                    @elseif($this->tab_id == 49)
                        <livewire:accounting.interest-receivable />
                    @elseif($this->tab_id == 26)
                        <livewire:accounting.long-term-and-short-term />
                    @elseif($this->tab_id == 27)
                        <livewire:accounting.unearned />
                    @elseif($this->tab_id == 28)
                        <livewire:accounting.statement-of-changes-in-equity />
                    @elseif($this->tab_id == 29)
                        <livewire:accounting.investiments />
                    @elseif($this->tab_id == 30)
                        <livewire:accounting.trade-and-other-payables />
                    @elseif($this->tab_id == 32)
                        <livewire:accounting.depreciation />
                    @elseif($this->tab_id == 33)
                        <livewire:accounting.member-share />
                    @elseif($this->tab_id == 34)
                        <livewire:accounting.member-saving />
                    @elseif($this->tab_id == 35)
                        <livewire:accounting.member-deposit />
                    @elseif($this->tab_id == 36)
                        <livewire:accounting.loan-out-standing />
                    @elseif($this->tab_id == 38)
                        <livewire:accounting.loan-charges />
                    @elseif($this->tab_id == 39)
                        <livewire:accounting.insurance-charges />
                    @elseif($this->tab_id == 40)
                        <livewire:accounting.comparative-income-expense />
                    @elseif($this->tab_id == 41)
                        <livewire:accounting.notes-to-accounts />
                    @elseif($this->tab_id == 42)
                        <livewire:accounting.statement-of-financial-position />
                    @elseif($this->tab_id == 50)
                        <livewire:accounting.integrated-financial-statements />
                    @elseif($this->tab_id == 22)
                        <livewire:accounting.financial-insurance />
                    @elseif($this->tab_id == 24)
                        <livewire:accounting.creditors />
                        @elseif($this->tab_id == 43)
                            {{-- Approvers Manager --}}
                  
                    @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

{{-- Enhanced CSS for modern accounting interface --}}
<style nonce="{{ csp_nonce() }}">
.scrollbar-thin {
    scrollbar-width: thin;
}

.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 2px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb {
    background: #4b5563;
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #6b7280;
}

/* Enhanced smooth animations */
.transition-all {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Card hover effects */
.hover\:shadow-xl:hover {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Button hover effects */
.group:hover .group-hover\:scale-110 {
    transform: scale(1.1);
}

.group:hover .group-hover\:translate-x-1 {
    transform: translateX(0.25rem);
}

/* Loading states */
@keyframes shimmer {
    0% {
        background-position: -200px 0;
    }
    100% {
        background-position: calc(200px + 100%) 0;
    }
}

.loading-shimmer {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 37%, #f0f0f0 63%);
    background-size: 400px 100%;
    animation: shimmer 1.5s ease-in-out infinite;
}

/* Active state animations */
.bg-blue-900 {
    animation: subtle-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes subtle-pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.95;
    }
}

/* Card gradient animations */
.gradient-animation {
    background-size: 200% 200%;
    animation: gradient-shift 3s ease infinite;
}

@keyframes gradient-shift {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

/* Enhanced focus states */
.focus\:ring-blue-500:focus {
    --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
    --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(3px + var(--tw-ring-offset-width)) var(--tw-ring-color);
    box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
    --tw-ring-color: rgb(59 130 246 / 0.5);
}

/* Microinteractions */
.micro-bounce:hover {
    animation: micro-bounce 0.6s ease-in-out;
}

@keyframes micro-bounce {
    0%, 20%, 53%, 80%, 100% {
        transform: translate3d(0,0,0);
    }
    40%, 43% {
        transform: translate3d(0, -2px, 0);
    }
    70% {
        transform: translate3d(0, -1px, 0);
    }
    90% {
        transform: translate3d(0, -1px, 0);
    }
}

/* Table enhancements */
.table-row-hover:hover {
    background: linear-gradient(90deg, rgba(59, 130, 246, 0.05), rgba(59, 130, 246, 0.02));
    transform: translateX(2px);
}

/* Enhanced shadows */
.enhanced-shadow {
    box-shadow: 
        0 1px 3px 0 rgba(0, 0, 0, 0.1),
        0 1px 2px 0 rgba(0, 0, 0, 0.06),
        0 0 0 1px rgba(0, 0, 0, 0.05);
}

.enhanced-shadow:hover {
    box-shadow: 
        0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06),
        0 0 0 1px rgba(0, 0, 0, 0.05);
}

/* Success/Error states */
.success-pulse {
    animation: success-pulse 1s ease-in-out;
}

@keyframes success-pulse {
    0% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
    }
    70% {
        transform: scale(1.02);
        box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
    }
}

/* Content transitions */
.fade-in {
    animation: fade-in 0.5s ease-in-out;
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive enhancements */
@media (max-width: 768px) {
    .mobile-optimized {
        padding: 1rem;
        font-size: 0.875rem;
    }
    
    .mobile-stack {
        flex-direction: column;
        gap: 1rem;
    }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
    .auto-dark {
        --tw-bg-opacity: 1;
        background-color: rgb(31 41 55 / var(--tw-bg-opacity));
        --tw-text-opacity: 1;
        color: rgb(243 244 246 / var(--tw-text-opacity));
    }
}
</style>



