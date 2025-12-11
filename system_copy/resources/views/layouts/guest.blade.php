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

        <!-- Scripts -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script nonce="{{ csp_nonce() }}">
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: {
                                50: '#f0f9ff',
                                100: '#e0f2fe',
                                200: '#bae6fd',
                                300: '#7dd3fc',
                                400: '#38bdf8',
                                500: '#0ea5e9',
                                600: '#0284c7',
                                700: '#0369a1',
                                800: '#075985',
                                900: '#0c4a6e',
                            }
                        },
                        fontFamily: {
                            sans: ['Inter', 'sans-serif'],
                        },
                    }
                }
            }
        </script>

        <style nonce="{{ csp_nonce() }}">
            [x-cloak] { display: none !important; }

            .bg-gradient {
                background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            }
        </style>
    </head>
    <body class="h-full font-sans antialiased">
        <div class="min-h-screen bg-gradient">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                                    <defs>
                        <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5"/>
                        </pattern>
                                    </defs>
                    <rect width="100" height="100" fill="url(#grid)" />
                                </svg>
            </div>

            <!-- Header -->
            <header class="relative">
                <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" aria-label="Top">
                    <div class="flex w-full items-center justify-between py-6">
                        <div class="flex items-center">
                            <a href="/" class="flex items-center space-x-3">
                                <img class="h-10 w-auto" src="{{ asset('/images/nbc.png') }}" alt="NBC Logo">
                                <span class="text-white text-xl font-semibold">Saccos Management</span>
                            </a>
                        </div>
                        <div class="ml-10 space-x-4">
                            <a href="{{ route('login') }}" class="inline-block rounded-md border border-transparent bg-white px-4 py-2 text-base font-medium text-primary-600 hover:bg-primary-50">Sign in</a>
                            <a href="{{ route('register') }}" class="inline-block rounded-md border border-transparent bg-primary-500 px-4 py-2 text-base font-medium text-white hover:bg-primary-600">Sign up</a>
                        </div>
                    </div>
                </nav>
            </header>

            <!-- Main Content -->
            <main class="relative">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
                    <div class="text-center">
                        <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl md:text-6xl">
                            <span class="block">Welcome to</span>
                            <span class="block text-primary-200">Saccos Management</span>
                        </h1>
                        <p class="mx-auto mt-3 max-w-md text-base text-primary-100 sm:text-lg md:mt-5 md:max-w-3xl md:text-xl">
                            A better way of saving, is together! Join our community and start your journey towards financial freedom.
                        </p>
                    </div>

                    <!-- Content Area -->
                    <div class="mt-12">
                        {{ $slot }}
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="relative mt-12 bg-white">
                <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">About</h3>
                            <ul class="mt-4 space-y-4">
                                <li>
                                    <a href="#" class="text-base text-gray-500 hover:text-gray-900">About Us</a>
                                </li>
                                <li>
                                    <a href="#" class="text-base text-gray-500 hover:text-gray-900">Careers</a>
                                </li>
                                <li>
                                    <a href="#" class="text-base text-gray-500 hover:text-gray-900">Blog</a>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Support</h3>
                            <ul class="mt-4 space-y-4">
                                <li>
                                    <a href="#" class="text-base text-gray-500 hover:text-gray-900">Help Center</a>
                                </li>
                                <li>
                                    <a href="#" class="text-base text-gray-500 hover:text-gray-900">Contact Us</a>
                                </li>
                                <li>
                                    <a href="#" class="text-base text-gray-500 hover:text-gray-900">Privacy</a>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Legal</h3>
                            <ul class="mt-4 space-y-4">
                                <li>
                                    <a href="#" class="text-base text-gray-500 hover:text-gray-900">Privacy Policy</a>
                                </li>
                                <li>
                                    <a href="#" class="text-base text-gray-500 hover:text-gray-900">Terms of Service</a>
                                </li>
                                <li>
                                    <a href="#" class="text-base text-gray-500 hover:text-gray-900">Cookie Policy</a>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Connect</h3>
                            <ul class="mt-4 space-y-4">
                                <li>
                                    <a href="#" class="text-base text-gray-500 hover:text-gray-900">Facebook</a>
                                </li>
                                <li>
                                    <a href="#" class="text-base text-gray-500 hover:text-gray-900">Twitter</a>
                                </li>
                                <li>
                                    <a href="#" class="text-base text-gray-500 hover:text-gray-900">LinkedIn</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-8 border-t border-gray-200 pt-8">
                        <p class="text-base text-gray-400 text-center">
                            &copy; {{ date('Y') }} Saccos Management System. All rights reserved.
                        </p>
                    </div>
            </div>
            </footer>
            </div>

        <!-- Scripts -->
    <livewire:scripts />
    <script src="{{ asset('js/app.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>
    </body>
</html>
