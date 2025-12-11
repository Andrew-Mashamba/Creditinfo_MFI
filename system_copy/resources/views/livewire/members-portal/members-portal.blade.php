<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Members Portal & Mobile</h1>
                <p class="text-gray-600 mt-1">Manage member portal access, mobile app features, and member self-service capabilities</p>
            </div>
            <div class="flex items-center space-x-3">
                <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                    <span class="w-2 h-2 bg-green-400 rounded-full inline-block mr-2"></span>
                    Active
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="mb-6">
        <nav class="flex space-x-8 border-b border-gray-200">
            @if($permissions['canView'] ?? false)
            <button wire:click="setActiveTab('portal-settings')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                    {{ $activeTab === 'portal-settings' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Portal Settings
            </button>
            @else
            <span class="py-2 px-1 border-b-2 font-medium text-sm text-gray-400 cursor-not-allowed opacity-50" title="No permission to access this section">
                Portal Settings
            </span>
            @endif
            
            @if($permissions['canView'] ?? false)
            <button wire:click="setActiveTab('mobile-app')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                    {{ $activeTab === 'mobile-app' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Mobile App
            </button>
            @else
            <span class="py-2 px-1 border-b-2 font-medium text-sm text-gray-400 cursor-not-allowed opacity-50" title="No permission to access this section">
                Mobile App
            </span>
            @endif
            
            @if($permissions['canManageUsers'] ?? $permissions['canView'] ?? false)
            <button wire:click="setActiveTab('user-management')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                    {{ $activeTab === 'user-management' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                User Management
            </button>
            @else
            <span class="py-2 px-1 border-b-2 font-medium text-sm text-gray-400 cursor-not-allowed opacity-50" title="No permission to access this section">
                User Management
            </span>
            @endif
            
            @if($permissions['canViewAnalytics'] ?? $permissions['canView'] ?? false)
            <button wire:click="setActiveTab('analytics')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                    {{ $activeTab === 'analytics' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Analytics
            </button>
            @else
            <span class="py-2 px-1 border-b-2 font-medium text-sm text-gray-400 cursor-not-allowed opacity-50" title="No permission to access this section">
                Analytics
            </span>
            @endif
        </nav>
    </div>

    <!-- Content Area -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        @if($permissions['canView'] ?? false)
            @if($activeTab === 'portal-settings')
                @if($permissions['canManageSettings'] ?? $permissions['canView'] ?? false)
                    @include('livewire.members-portal.partials.portal-settings')
                @else
                    <div class="p-8 text-center">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <p class="text-gray-500">You don't have permission to manage portal settings.</p>
                    </div>
                @endif
            @elseif($activeTab === 'mobile-app')
                @if($permissions['canManageMobileApp'] ?? $permissions['canView'] ?? false)
                    @include('livewire.members-portal.partials.mobile-app')
                @else
                    <div class="p-8 text-center">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <p class="text-gray-500">You don't have permission to manage mobile app settings.</p>
                    </div>
                @endif
            @elseif($activeTab === 'user-management')
                @if($permissions['canManageUsers'] ?? $permissions['canView'] ?? false)
                    @include('livewire.members-portal.partials.user-management')
                @else
                    <div class="p-8 text-center">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <p class="text-gray-500">You don't have permission to manage users.</p>
                    </div>
                @endif
            @elseif($activeTab === 'analytics')
                @if($permissions['canViewAnalytics'] ?? $permissions['canView'] ?? false)
                    @include('livewire.members-portal.partials.analytics')
                @else
                    <div class="p-8 text-center">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <p class="text-gray-500">You don't have permission to view analytics.</p>
                    </div>
                @endif
            @endif
        @else
            <div class="p-8 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Access Restricted</h3>
                <p class="text-gray-600">You don't have permission to access the members portal.</p>
                <p class="text-sm text-gray-500 mt-2">Please contact your administrator if you need access.</p>
            </div>
        @endif
    </div>
</div> 