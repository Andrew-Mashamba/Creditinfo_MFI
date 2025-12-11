<div class="w-80 rounded-2xl shadow-lg border border-gray-100 bg-white overflow-hidden flex flex-col h-full">
    <!-- Logo at the top -->
    <div class="flex items-center justify-center py-6 border-b border-gray-100 bg-white">
        <img src="{{ asset('images/logo-onwhite.svg') }}" alt="MFI Logo" class="h-auto w-64 object-contain" loading="lazy" />
    </div>

    <!-- Navigation -->
    <div class="p-4 flex-1 flex flex-col overflow-y-auto sidebar-scroll max-h-screen">


        <nav class="space-y-2 flex-1">
            <!-- Dashboard -->
            <button wire:click="menuItemClicked(0)" class="relative w-full group transition-all duration-200">
                <div
                    class="flex items-center p-3 rounded-xl transition-all duration-200
                    {{ $tab_id === 0 ? 'bg-red-600 text-white shadow-lg' : 'bg-gray-50 hover:bg-gray-200 text-gray-700 hover:text-gray-900' }}">


                    <!-- Loading State -->
                    <div wire:loading wire:target="menuItemClicked({{ 0 }})" class="mr-3">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle>
                            <path class="opacity-75" fill="red"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>

                    <!-- Icon -->
                    <div wire:loading.remove wire:target="menuItemClicked({{ 0 }})" class="mr-3">
                        <div class="mr-3">
                            @include('livewire.sidebar.menu-icons', ['menuId' => 0])
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 text-left">
                        <div class="font-medium text-sm">Dashboard</div>
                        <div class="text-xs opacity-75">Overview of all the data</div>
                    </div>


                </div>
            </button>
            <!-- Dynamic Menu Items -->
            @foreach($this->menuItems as $menuId)
                @if($menuId == 3 || $menuId == 4 || $menuId == 5 || $menuId == 10 || $menuId == 11 || $menuId == 12 || $menuId == 14 || $menuId == 15 || $menuId == 24 || $menuId == 25 || $menuId == 26 || $menuId == 29 || $menuId == 30)
                    @continue
                @endif
                @if($menuId == 0)
                    @continue {{-- Dashboard is already handled above --}}
                @endif
                @php $menu = \App\Models\Menu::find($menuId); @endphp
                @if(!$menu)
                    @continue {{-- Skip if menu not found --}}
                @endif
                <button wire:click="menuItemClicked({{$menuId}})" class="relative w-full group transition-all duration-200">
                    <div
                        class="flex items-center p-3 rounded-xl transition-all duration-200
                            {{ $tab_id == $menuId ? 'bg-red-600 text-white shadow-lg' : 'bg-gray-50 hover:bg-gray-200 text-gray-700 hover:text-gray-900' }}">

                        <!-- Loading State -->
                        <div wire:loading wire:target="menuItemClicked({{ $menuId }})" class="mr-3">
                            <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="red"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>

                        <!-- Icon -->
                        <div wire:loading.remove wire:target="menuItemClicked({{ $menuId }})" class="mr-3">
                            <div class="mr-3">
                                @include('livewire.sidebar.menu-icons', ['menuId' => $menuId])
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 text-left">
                            <div class="font-medium text-sm">{{ $menu->menu_name }} </div>
                            <div class="text-xs opacity-75">{{ $menu->menu_description }}</div>
                        </div>

                    </div>
                </button>
            @endforeach
        </nav>


    </div>

    <!-- Add this to your CSS or style tag -->
    <style nonce="{{ csp_nonce() }}">
        @keyframes ripple {
            0% {
                transform: scale(0);
                opacity: 0.3;
            }

            100% {
                transform: scale(10);
                opacity: 0;
            }
        }

        .animate-ripple {
            animation: ripple 0.6s linear;
        }

        /* Custom red vertical scrollbar for sidebar menu */
        .sidebar-scroll::-webkit-scrollbar {
            width: 2px;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #e3342f;
            border-radius: 2px;
        }

        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        /* For Firefox */
        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: #e3342f transparent;
        }
    </style>
</div>


