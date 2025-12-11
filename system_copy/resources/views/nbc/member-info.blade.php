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
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">NBC Member Information</h1>
                    <p class="text-gray-600 mt-2">Member Number: {{ $memberNumber }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                        {{ $memberInfo['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($memberInfo['status']) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Member Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Personal Information</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <p class="mt-1 text-lg text-gray-900">{{ $memberInfo['member_name'] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <p class="mt-1 text-lg text-gray-900">{{ $memberInfo['phone_number'] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <p class="mt-1 text-lg text-gray-900">{{ $memberInfo['email'] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <p class="mt-1 text-lg text-gray-900">{{ $memberInfo['address'] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Branch</label>
                        <p class="mt-1 text-lg text-gray-900">{{ $memberInfo['branch'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Financial Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Financial Information</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account Balance</label>
                        <p class="mt-1 text-2xl font-bold text-green-600">TZS {{ $memberInfo['account_balance'] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Savings Balance</label>
                        <p class="mt-1 text-xl text-blue-600">TZS {{ $memberInfo['savings_balance'] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Loan Balance</label>
                        <p class="mt-1 text-xl text-red-600">TZS {{ $memberInfo['loan_balance'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Details -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Account Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Registration Date</label>
                    <p class="mt-1 text-lg text-gray-900">{{ \Carbon\Carbon::parse($memberInfo['registration_date'])->format('F j, Y') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                    <p class="mt-1 text-lg text-gray-900">{{ \Carbon\Carbon::parse($memberInfo['last_updated'])->format('F j, Y g:i A') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Member Status</label>
                    <p class="mt-1 text-lg text-gray-900">{{ ucfirst($memberInfo['status']) }}</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <div class="flex flex-wrap gap-4">
                <button class="bg-blue-900 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-print mr-2"></i>Print Statement
                </button>
                <button class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-download mr-2"></i>Download PDF
                </button>
                <button class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-edit mr-2"></i>Edit Information
                </button>
                <button class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </button>
            </div>
        </div>

        <!-- Debug Information (remove in production) -->
        @if(config('app.debug'))
        <div class="bg-gray-100 rounded-lg p-4 mt-6">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Debug Information</h3>
            <p class="text-xs text-gray-600">Ignore Parameter: {{ $ignoreParameter }}</p>
            <p class="text-xs text-gray-600">Request Time: {{ now() }}</p>
        </div>
        @endif
    </div>
</div>



<script nonce="{{ csp_nonce() }}">
    // Add any JavaScript functionality here
    document.addEventListener('DOMContentLoaded', function() {
        console.log('NBC Member Information page loaded');
        
        // Example: Add print functionality
        document.querySelector('button:contains("Print Statement")').addEventListener('click', function() {
            window.print();
        });
    });
</script>


</body>
</html>