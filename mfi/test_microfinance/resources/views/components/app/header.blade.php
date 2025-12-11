<header class="sticky top-0 bg-white border-b border-gray-200/50 shadow-sm z-50">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Header: Left side -->
            <div class="flex items-center space-x-4">
            

                <!-- Logo and Title -->
                <div class="flex items-center space-x-3">
                
                    <div class="hidden sm:block">
                        <h1 class="text-lg font-bold text-gray-900">MFI MANAGEMENT SYSTEM</h1>
                        <p class="text-xs text-gray-500">MFI Management System</p>
                    </div>
                </div>
            </div>

            <!-- Header: Center - Breadcrumb/Page Title -->
            <div class="hidden md:flex items-center justify-center flex-1 px-8">
                <div class="text-center">
                    <h2 class="text-sm font-medium text-gray-600">Welcome back, {{ Auth::user()->name }}</h2>
                    <div class="flex items-center justify-center space-x-2 mt-1">
                        @php
                            $userRoles = Auth::user()->roles;
                            $primaryRole = $userRoles->first();
                        @endphp
                        @if($primaryRole)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 01-8 0M12 15v2m0 4h.01M6.938 19h10.124c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                {{ $primaryRole->name }}
                            </span>
                        @endif
                        @if($userRoles->count() > 1)
                            <span class="text-xs text-gray-400">+{{ $userRoles->count() - 1 }} more</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Header: Right side -->
            <div class="flex items-center space-x-3">
         
                <!-- AI Chat Button -->
                <button
                    id="ai-chat-button"
                    class="text-white bg-red-600 hover:from-red-700 hover:to-indigo-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-full
                    text-sm px-1 py-1 text-center inline-flex items-center transition-all duration-200 shadow-sm"
                    title="AI Assistant Chat">
                    <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <span class="hidden sm:inline">AI Assistant</span>
                </button>

                <!-- User Profile Dropdown -->
                <div class="relative">
                    <button
                        class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        id="user-menu-button"
                        aria-expanded="false"
                        aria-haspopup="true">
                        
                        <!-- User Avatar -->
                        <div class="relative">
                            <img class="w-8 h-8 rounded-full ring-2 ring-gray-200" 
                                 src="{{ asset('images/avatar.png') }}" 
                                 alt="{{ Auth::user()->name }}" />
                            <div class="absolute -bottom-1 -right-1 h-3 w-3 bg-green-400 rounded-full border-2 border-white"></div>
                        </div>

                        <!-- User Info -->
                        <div class="hidden sm:block text-left">
                            <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-gray-500">
                                @if($primaryRole)
                                    {{ $primaryRole->name }}
                                @else
                                    User
                                @endif
                            </div>
                        </div>

                        <!-- Dropdown Arrow -->
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="user-dropdown" 
                         class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 py-1 z-50 transition-all duration-200 opacity-0 scale-95"
                         role="menu"
                         aria-orientation="vertical"
                         aria-labelledby="user-menu-button"
                         tabindex="-1">
                        
                        <!-- User Info Section -->
                        <div class="px-4 py-3 border-b border-gray-100">
                            <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                            <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
                            @if($primaryRole)
                                <div class="text-xs text-indigo-600 mt-1">{{ $primaryRole->name }}</div>
                            @endif
                        </div>

                      

                        <div class="border-t border-gray-100"></div>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" 
                                    class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150" 
                                    role="menuitem">
                                <svg class="w-4 h-4 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>


                
            </div>
        </div>
    </div>

<script nonce="{{ csp_nonce() }}">
// AI Chat Button Handler
const aiChatButton = document.getElementById('ai-chat-button');
if (aiChatButton) {
    aiChatButton.addEventListener('click', function() {
        if (typeof openChatModal === 'function') {
            openChatModal();
        }
    });
}

// User Menu Button Handler
const userMenuButton = document.getElementById('user-menu-button');
if (userMenuButton) {
    userMenuButton.addEventListener('click', toggleUserDropdown);
}

function toggleUserDropdown() {
    const dropdown = document.getElementById('user-dropdown');
    const button = document.getElementById('user-menu-button');
    
    if (dropdown.classList.contains('hidden')) {
        // Show dropdown
        dropdown.classList.remove('hidden');
        dropdown.classList.remove('opacity-0', 'scale-95');
        dropdown.classList.add('opacity-100', 'scale-100');
        button.setAttribute('aria-expanded', 'true');
    } else {
        // Hide dropdown
        dropdown.classList.add('hidden');
        dropdown.classList.remove('opacity-100', 'scale-100');
        dropdown.classList.add('opacity-0', 'scale-95');
        button.setAttribute('aria-expanded', 'false');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('user-dropdown');
    const button = document.getElementById('user-menu-button');
    
    if (!button.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.add('hidden');
        dropdown.classList.remove('opacity-100', 'scale-100');
        dropdown.classList.add('opacity-0', 'scale-95');
        button.setAttribute('aria-expanded', 'false');
    }
});

// Close dropdown on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const dropdown = document.getElementById('user-dropdown');
        const button = document.getElementById('user-menu-button');
        
        dropdown.classList.add('hidden');
        dropdown.classList.remove('opacity-100', 'scale-100');
        dropdown.classList.add('opacity-0', 'scale-95');
        button.setAttribute('aria-expanded', 'false');
    }
});
</script>

</header>

