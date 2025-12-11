<!-- Sidebar -->
<div class="bg-gradient-to-b from-gray-900 to-gray-800 text-white w-64 min-h-screen flex-shrink-0 shadow-2xl">
    <!-- Logo Section -->
    <div class="p-6 border-b border-gray-700">
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div class="ml-3">
                <h1 class="text-lg font-bold text-white">CreditInfo</h1>
                <p class="text-xs text-gray-300">MFI Admin Portal</p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="p-4 space-y-2">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="flex items-center p-3 rounded-lg text-gray-300 hover:bg-red-600 hover:text-white transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-red-600 text-white' : '' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v3H8V5z"/>
            </svg>
            Dashboard
        </a>

        <!-- MFI Management -->
        <div class="space-y-1">
            <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">MFI Management</h3>
            
            <a href="{{ route('mfi.all-institutions') }}" class="flex items-center p-3 rounded-lg text-gray-300 hover:bg-red-600 hover:text-white transition-all duration-200 {{ request()->routeIs('mfi.all-institutions') ? 'bg-red-600 text-white' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                All Institutions
            </a>
            
            <a href="{{ route('mfi.create-new') }}" class="flex items-center p-3 rounded-lg text-gray-300 hover:bg-red-600 hover:text-white transition-all duration-200 {{ request()->routeIs('mfi.create-new') ? 'bg-red-600 text-white' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create New MFI
            </a>
            
            <a href="{{ route('mfi.instance-configuration') }}" class="flex items-center p-3 rounded-lg text-gray-300 hover:bg-red-600 hover:text-white transition-all duration-200 {{ request()->routeIs('mfi.instance-configuration') ? 'bg-red-600 text-white' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Instance Configuration
            </a>
        </div>

        <!-- System Management -->
        <div class="space-y-1 pt-4">
            <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">System</h3>
            
            <a href="#" class="flex items-center p-3 rounded-lg text-gray-300 hover:bg-red-600 hover:text-white transition-all duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                User Management
            </a>
            
            <a href="#" class="flex items-center p-3 rounded-lg text-gray-300 hover:bg-red-600 hover:text-white transition-all duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Analytics & Reports
            </a>
            
            <a href="#" class="flex items-center p-3 rounded-lg text-gray-300 hover:bg-red-600 hover:text-white transition-all duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Security & Audit
            </a>
            
            <a href="#" class="flex items-center p-3 rounded-lg text-gray-300 hover:bg-red-600 hover:text-white transition-all duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                System Settings
            </a>
        </div>

        <!-- Database Management -->
        <div class="space-y-1 pt-4">
            <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Database</h3>
            
            <a href="#" class="flex items-center p-3 rounded-lg text-gray-300 hover:bg-red-600 hover:text-white transition-all duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                </svg>
                Database Status
            </a>
            
            <a href="#" class="flex items-center p-3 rounded-lg text-gray-300 hover:bg-red-600 hover:text-white transition-all duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Backup & Restore
            </a>
        </div>
    </nav>

</div>