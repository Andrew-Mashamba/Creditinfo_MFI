<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS CDN for development -->
        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body>
        <div class="min-h-screen bg-gradient-to-br from-gray-900 via-red-900 to-gray-800 flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="32" height="32" fill="none"%3E%3Cpath stroke="rgb(255 255 255 / 0.03)" stroke-width="1" d="m0 8 8-8M8 0l8 8-8 8M16 8l8-8M24 0l8 8-8 8M32 8v8M0 24l8-8M8 16l8 8-8 8M16 24l8-8M24 16l8 8-8 8"/%3E%3C/svg%3E')] opacity-20"></div>
            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
