<!-- Navigation Links -->
<div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
    <!-- Dashboard -->
    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
        {{ __('Dashboard') }}
    </x-nav-link>

    <!-- User Management -->
    <div class="relative" x-data="{ open: false }" @click.away="open = false">
        <button @click="open = !open" class="flex items-center text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            {{ __('User Management') }}
        </button>
        <div x-show="open" class="absolute z-50 mt-2 w-48 bg-white rounded-md shadow-lg">
            <a href="{{ route('users.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Users</a>
            <a href="{{ route('roles.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Roles</a>
            <a href="{{ route('permissions.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Permissions</a>
        </div>
    </div>

    <!-- Department Management -->
    <x-nav-link href="{{ route('departments.index') }}" :active="request()->routeIs('departments.*')">
        {{ __('Departments') }}
    </x-nav-link>

    <!-- Committee Management -->
    <x-nav-link href="{{ route('committees.index') }}" :active="request()->routeIs('committees.*')">
        {{ __('Committees') }}
    </x-nav-link>

    <!-- Settings -->
    <div class="relative" x-data="{ open: false }" @click.away="open = false">
        <button @click="open = !open" class="flex items-center text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ __('Settings') }}
        </button>
        <div x-show="open" class="absolute z-50 mt-2 w-48 bg-white rounded-md shadow-lg">
            <a href="{{ route('settings.user') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">User Settings</a>
            <a href="{{ route('settings.audit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Audit Logs</a>
        </div>
    </div>
</div> 