<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MFI Management System') }} - Error</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/flowbite.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style nonce="{{ csp_nonce() }}">
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="h-full font-sans antialiased">
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Error Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h1 class="text-2xl font-bold text-gray-900">NBC Error</h1>
                    <p class="text-gray-600 mt-1">Unable to retrieve member information</p>
                </div>
            </div>
        </div>

        <!-- Error Details -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Error Message</label>
                    <p class="mt-1 text-lg text-red-600">{{ $error }}</p>
                </div>
                
                @if(isset($member_number))
                <div>
                    <label class="block text-sm font-medium text-gray-700">Member Number</label>
                    <p class="mt-1 text-lg text-gray-900">{{ $member_number }}</p>
                </div>
                @endif

                <div class="border-t pt-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">What you can do:</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-600">
                        <li>Verify the member number is correct</li>
                        <li>Check if the member exists in the system</li>
                        <li>Contact system administrator if the problem persists</li>
                        <li>Try refreshing the page</li>
                    </ul>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex flex-wrap gap-4">
                <button onclick="history.back()" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Go Back
                </button>
                <button onclick="location.reload()" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-redo mr-2"></i>Try Again
                </button>
                <a href="{{ route('system') }}" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 inline-block">
                    <i class="fas fa-home mr-2"></i>Go to Dashboard
                </a>
            </div>
        </div>

        <!-- Support Information -->
        <div class="bg-red-50 rounded-lg p-4 mt-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Need Help?</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>If you continue to experience issues, please contact the system administrator or technical support team.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
    document.addEventListener('DOMContentLoaded', function() {
        console.log('NBC Error page loaded');
    });
</script>

</body>
</html> 