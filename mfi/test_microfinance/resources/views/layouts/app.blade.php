<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Saccos Management System') }}</title>




    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <livewire:styles />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/flowbite.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <livewire:scripts />
    
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style nonce="{{ csp_nonce() }}">
        [x-cloak] {
            display: none !important;
        }
    </style>
    @powerGridStyles


</head>

<body class="h-full font-sans antialiased">
    <!-- Page wrapper of all pages -->
















    <div class="flex h-screen bg-gray-100">

        <!-- sidebar -->
        <div class="hidden md:flex flex-col">
            <livewire:sidebar.sidebar />
        </div>

        <!-- Main content -->
        <div class="flex flex-col flex-1 overflow-y-auto scrollbar">
            <x-app.header />
            <!-- Main content area -->
            <main class="flex-1 overflow-y-auto pl-6 scrollbar">
                {{ $slot }}
            </main>

        </div>


        <style nonce="{{ csp_nonce() }}">
        /* Custom red vertical scrollbar for branches table */
        .scrollbar::-webkit-scrollbar {
            width: 2px;
        }
        .scrollbar::-webkit-scrollbar-thumb {
            background: #e3342f;
            border-radius: 2px;
        }
        .scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        /* For Firefox */
        .scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #e3342f transparent;
        }
    </style>

    </div>






















    @powerGridScripts

    <!-- Additional Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/lodash/lodash.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/gaugeJS@1.3.7/dist/gauge.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" defer></script>
    
    @stack('scripts')

    <!-- AI Chat Modal -->
    <div id="chatModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">

        {{-- AI Agent component removed for security reasons --}}


        </div>
    </div>



    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('livewire:load', function() {
            Livewire.on('navigateTo', component => {
                Livewire.emit('switchComponent', component);
            });
        });

        // Chat Modal Functions
        function openChatModal() {
            document.getElementById('chatModal').classList.remove('hidden');
            document.getElementById('chatInput').focus();
        }

        function closeChatModal() {
            document.getElementById('chatModal').classList.add('hidden');
        }



        // Close modal when clicking outside
        document.getElementById('chatModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeChatModal();
            }
        });
    </script>




    <script nonce="{{ csp_nonce() }}">
        // User menu functionality (using vanilla JavaScript instead of Alpine.js)
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuButtons = document.querySelectorAll('[data-user-menu]');
            userMenuButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const menu = this.nextElementSibling;
                    if (menu) {
                        menu.classList.toggle('hidden');
                    }
                });
            });
            
            // Close menus when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('[data-user-menu]')) {
                    document.querySelectorAll('[data-user-menu-dropdown]').forEach(menu => {
                        menu.classList.add('hidden');
                    });
                }
            });
        });

        // Scroll to category function
        function scrollToCategory(categoryId) {
            const element = document.getElementById(categoryId);
            if (!element) return;

            element.scrollIntoView({
                behavior: 'smooth'
            });
            highlightTable(categoryId);
        }

        // Highlight table function
        function highlightTable(categoryId) {
            const table = document.getElementById(categoryId);
            if (!table) return;

            const originalBorderColor = table.style.borderColor;
            const originalMarginTop = table.style.marginTop;
            const thead = table.querySelector('thead');
            const tfoot = table.querySelector('tfoot');
            const originalTheadBg = thead ? thead.style.backgroundColor : null;
            const originalTfootBg = tfoot ? tfoot.style.backgroundColor : null;

            table.style.borderColor = '#f87171';
            table.style.marginTop = '100px';

            if (thead) thead.style.backgroundColor = '#f87171';
            if (tfoot) tfoot.style.backgroundColor = '#f87171';

            setTimeout(() => {
                table.style.borderColor = originalBorderColor;
                table.style.marginTop = originalMarginTop;
                if (thead) thead.style.backgroundColor = originalTheadBg;
                if (tfoot) tfoot.style.backgroundColor = originalTfootBg;
            }, 3000);
        }
    </script>

</body>

</html>