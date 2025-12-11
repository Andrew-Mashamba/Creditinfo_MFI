<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>MFI Admin Portal - {{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gradient-to-br from-gray-900 via-red-900 to-gray-900 min-h-screen flex relative overflow-hidden">
        <!-- Animated gradient overlay -->
        <div class="absolute inset-0 bg-gradient-to-br from-black via-red-900 to-black opacity-60"></div>
        
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-red-600 to-red-900 opacity-20 rounded-full blur-3xl transform translate-x-48 -translate-y-48"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-gradient-to-tr from-red-800 to-red-600 opacity-20 rounded-full blur-3xl transform -translate-x-48 translate-y-48"></div>
        
        <!-- Header Navigation -->
        <header class="absolute top-0 right-0 p-6 z-20">
            @if (Route::has('login'))
                <nav class="flex items-center gap-4">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-block px-6 py-2 text-white border border-white/20 hover:border-red-400 rounded-lg text-sm font-medium transition-all duration-200 backdrop-blur-sm bg-white/10"
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-block px-6 py-2 text-white hover:text-red-300 text-sm font-medium transition-colors duration-200"
                        >
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-block px-6 py-2 text-white border border-white/20 hover:border-red-400 rounded-lg text-sm font-medium transition-all duration-200 backdrop-blur-sm bg-white/10"
                            >
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <div class="flex-1 relative z-10 flex items-center justify-center p-12">
            <div class="max-w-4xl w-full">
                <!-- Main branding and content card -->
                <div class="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-2xl rounded-3xl p-12 shadow-2xl border border-white/20">
                    <div class="text-center mb-12">
                        <!-- Logo and Branding -->
                        <div class="flex items-center justify-center mb-8">
                            <div class="w-20 h-20 rounded-2xl flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);">
                                <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                            </div>
                        </div>
                        
                        <h1 class="text-6xl font-black text-white mb-4 tracking-tight">MFI Admin Portal</h1>
                        <div class="h-2 w-48 mx-auto rounded-full mb-6" style="background: linear-gradient(90deg, #DC2626 0%, #B91C1C 50%, transparent 100%);"></div>
                        <p class="text-2xl font-bold text-white tracking-wide mb-4">Microfinance Management System</p>
                        <p class="text-white/80 text-lg leading-relaxed max-w-2xl mx-auto">
                            Centralized administration portal for managing multiple microfinance institutions. 
                            Create, configure, and monitor MFI instances with enterprise-grade security and scalability.
                        </p>
                    </div>

                    <!-- Feature Grid -->
                    <div class="grid md:grid-cols-3 gap-6 mb-10">
                        <div class="text-center p-6 rounded-xl bg-white/5 backdrop-blur-sm border border-white/10">
                            <div class="w-12 h-12 bg-red-600 rounded-lg mx-auto mb-4 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <h3 class="text-white font-semibold mb-2">Multi-Tenant Architecture</h3>
                            <p class="text-white/70 text-sm">Isolated instances for each MFI with dedicated databases and configurations</p>
                        </div>

                        <div class="text-center p-6 rounded-xl bg-white/5 backdrop-blur-sm border border-white/10">
                            <div class="w-12 h-12 bg-red-600 rounded-lg mx-auto mb-4 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <h3 class="text-white font-semibold mb-2">Enterprise Security</h3>
                            <p class="text-white/70 text-sm">Bank-grade security with role-based access control and audit trails</p>
                        </div>

                        <div class="text-center p-6 rounded-xl bg-white/5 backdrop-blur-sm border border-white/10">
                            <div class="w-12 h-12 bg-red-600 rounded-lg mx-auto mb-4 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <h3 class="text-white font-semibold mb-2">Automated Provisioning</h3>
                            <p class="text-white/70 text-sm">Instant deployment of new MFI instances with automated setup and configuration</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center px-8 py-3 rounded-xl text-white font-bold text-lg shadow-2xl hover:shadow-red-500/50 transform hover:-translate-y-1 hover:scale-105 active:translate-y-0 active:scale-100 transition-all duration-200 overflow-hidden group" style="background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);">
                                <span class="relative z-10">Access Dashboard</span>
                                <div class="absolute inset-0 bg-gradient-to-r from-red-500 to-red-700 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-3 rounded-xl text-white font-bold text-lg shadow-2xl hover:shadow-red-500/50 transform hover:-translate-y-1 hover:scale-105 active:translate-y-0 active:scale-100 transition-all duration-200 overflow-hidden group" style="background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);">
                                <span class="relative z-10">Administrator Login</span>
                                <div class="absolute inset-0 bg-gradient-to-r from-red-500 to-red-700 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                            </a>
                            
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-3 rounded-xl text-white font-bold text-lg border-2 border-white/20 hover:border-white/40 backdrop-blur-sm bg-white/10 hover:bg-white/20 transition-all duration-200">
                                    Request Access
                                </a>
                            @endif
                        @endauth
                    </div>

                    <!-- Security Notice -->
                    <div class="mt-10 pt-8 border-t-2 border-white/10">
                        <div class="flex items-center justify-center space-x-2 text-sm text-white/60">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">Secured by <span class="font-black text-white">Enterprise</span> Encryption</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>