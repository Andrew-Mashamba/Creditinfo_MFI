<div class="min-h-screen bg-gradient-to-br from-red-900 via-red-800 to-indigo-900">
    <!-- Loading Overlay -->
    <div wire:loading class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
            <span class="text-gray-700">Loading...</span>
        </div>
    </div>

    <!-- Login Form -->
    @if($showLogin)
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-white rounded-full flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-white mb-2">SACCO Members Portal</h2>
                <p class="text-red-200">Sign in to access your account</p>
            </div>

            <!-- Alert Messages -->
            @if (session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Login Form -->
            <form wire:submit.prevent="login" class="mt-8 space-y-6">
                <div class="space-y-4">
                    <!-- Login Identifier -->
                    <div>
                        <label for="loginIdentifier" class="block text-sm font-medium text-white mb-2">
                            Member Number, Phone, or Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <input wire:model.defer="loginIdentifier" id="loginIdentifier" type="text" required
                                class="appearance-none relative block w-full px-3 py-3 pl-10 pr-3 text-white placeholder-red-200 bg-red-800 bg-opacity-50 border border-red-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('loginIdentifier') border-red-400 @enderror"
                                placeholder="Enter your member number, phone, or email">
                        </div>
                        @error('loginIdentifier') <span class="text-red-300 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="loginPassword" class="block text-sm font-medium text-white mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input wire:model.defer="loginPassword" id="loginPassword" type="password" required
                                class="appearance-none relative block w-full px-3 py-3 pl-10 pr-10 text-white placeholder-red-200 bg-red-800 bg-opacity-50 border border-red-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('loginPassword') border-red-400 @enderror"
                                placeholder="Enter your password">
                            <button type="button" onclick="togglePassword('loginPassword')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-red-200 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        @error('loginPassword') <span class="text-red-300 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input wire:model.defer="rememberMe" id="rememberMe" type="checkbox"
                                class="h-4 w-4 text-red-600 focus:ring-red-500 border-red-300 rounded bg-transparent">
                            <label for="rememberMe" class="ml-2 block text-sm text-white">
                                Remember me
                            </label>
                        </div>
                        <div class="text-sm">
                            <button type="button" wire:click="showForgotPasswordForm" class="font-medium text-red-200 hover:text-white transition-colors duration-200">
                                Forgot password?
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" wire:loading.attr="disabled"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                        <span wire:loading.remove wire:target="login">Sign In</span>
                        <span wire:loading wire:target="login">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Signing In...
                        </span>
                    </button>
                </div>

                <!-- Register Link -->
                <div class="text-center">
                    <p class="text-red-200">
                        Don't have portal access? 
                        <button type="button" wire:click="showRegistrationForm" class="font-medium text-white hover:text-red-200 transition-colors duration-200">
                            Register here
                        </button>
                    </p>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Registration Form -->
    @if($showRegister)
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-white rounded-full flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-white mb-2">Register for Portal Access</h2>
                <p class="text-red-200">Create your portal account to access your SACCO services</p>
            </div>

            <!-- Registration Form -->
            <form wire:submit.prevent="register" class="mt-8 space-y-6">
                <div class="space-y-4">
                    <!-- Member Number -->
                    <div>
                        <label for="member_number" class="block text-sm font-medium text-white mb-2">Member Number</label>
                        <input wire:model.defer="registrationData.member_number" id="member_number" type="text" required
                            class="appearance-none relative block w-full px-3 py-3 text-white placeholder-red-200 bg-red-800 bg-opacity-50 border border-red-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('registrationData.member_number') border-red-400 @enderror"
                            placeholder="Enter your member number">
                        @error('registrationData.member_number') <span class="text-red-300 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-white mb-2">Phone Number</label>
                        <input wire:model.defer="registrationData.phone_number" id="phone_number" type="tel" required
                            class="appearance-none relative block w-full px-3 py-3 text-white placeholder-red-200 bg-red-800 bg-opacity-50 border border-red-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('registrationData.phone_number') border-red-400 @enderror"
                            placeholder="Enter your phone number">
                        @error('registrationData.phone_number') <span class="text-red-300 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-white mb-2">Email Address</label>
                        <input wire:model.defer="registrationData.email" id="email" type="email" required
                            class="appearance-none relative block w-full px-3 py-3 text-white placeholder-red-200 bg-red-800 bg-opacity-50 border border-red-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('registrationData.email') border-red-400 @enderror"
                            placeholder="Enter your email address">
                        @error('registrationData.email') <span class="text-red-300 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-white mb-2">First Name</label>
                        <input wire:model.defer="registrationData.first_name" id="first_name" type="text" required
                            class="appearance-none relative block w-full px-3 py-3 text-white placeholder-red-200 bg-red-800 bg-opacity-50 border border-red-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('registrationData.first_name') border-red-400 @enderror"
                            placeholder="Enter your first name">
                        @error('registrationData.first_name') <span class="text-red-300 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-white mb-2">Last Name</label>
                        <input wire:model.defer="registrationData.last_name" id="last_name" type="text" required
                            class="appearance-none relative block w-full px-3 py-3 text-white placeholder-red-200 bg-red-800 bg-opacity-50 border border-red-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('registrationData.last_name') border-red-400 @enderror"
                            placeholder="Enter your last name">
                        @error('registrationData.last_name') <span class="text-red-300 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-white mb-2">Password</label>
                        <input wire:model.defer="registrationData.password" id="password" type="password" required
                            class="appearance-none relative block w-full px-3 py-3 text-white placeholder-red-200 bg-red-800 bg-opacity-50 border border-red-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('registrationData.password') border-red-400 @enderror"
                            placeholder="Create a password (min 8 characters)">
                        @error('registrationData.password') <span class="text-red-300 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-white mb-2">Confirm Password</label>
                        <input wire:model.defer="registrationData.password_confirmation" id="password_confirmation" type="password" required
                            class="appearance-none relative block w-full px-3 py-3 text-white placeholder-red-200 bg-red-800 bg-opacity-50 border border-red-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Confirm your password">
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="flex items-center">
                        <input wire:model.defer="registrationData.terms_accepted" id="terms_accepted" type="checkbox" required
                            class="h-4 w-4 text-red-600 focus:ring-red-500 border-red-300 rounded bg-transparent">
                        <label for="terms_accepted" class="ml-2 block text-sm text-white">
                            I agree to the <a href="#" class="text-red-200 hover:text-white">Terms and Conditions</a>
                        </label>
                    </div>
                    @error('registrationData.terms_accepted') <span class="text-red-300 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" wire:loading.attr="disabled"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                        <span wire:loading.remove wire:target="register">Register</span>
                        <span wire:loading wire:target="register">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Registering...
                        </span>
                    </button>
                </div>

                <!-- Login Link -->
                <div class="text-center">
                    <p class="text-red-200">
                        Already have an account? 
                        <button type="button" wire:click="showLoginForm" class="font-medium text-white hover:text-red-200 transition-colors duration-200">
                            Sign in here
                        </button>
                    </p>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Forgot Password Form -->
    @if($showForgotPassword)
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-white rounded-full flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-white mb-2">Reset Password</h2>
                <p class="text-red-200">Enter your email to receive reset instructions</p>
            </div>

            <!-- Forgot Password Form -->
            <form wire:submit.prevent="forgotPassword" class="mt-8 space-y-6">
                <div>
                    <label for="resetEmail" class="block text-sm font-medium text-white mb-2">Email Address</label>
                    <input wire:model.defer="resetEmail" id="resetEmail" type="email" required
                        class="appearance-none relative block w-full px-3 py-3 text-white placeholder-red-200 bg-red-800 bg-opacity-50 border border-red-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('resetEmail') border-red-400 @enderror"
                        placeholder="Enter your email address">
                    @error('resetEmail') <span class="text-red-300 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" wire:loading.attr="disabled"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                        <span wire:loading.remove wire:target="forgotPassword">Send Reset Link</span>
                        <span wire:loading wire:target="forgotPassword">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Sending...
                        </span>
                    </button>
                </div>

                <!-- Back to Login -->
                <div class="text-center">
                    <button type="button" wire:click="showLoginForm" class="font-medium text-red-200 hover:text-white transition-colors duration-200">
                        ← Back to Sign In
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Dashboard -->
    @if($showDashboard && $memberData)
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <div class="h-10 w-10 bg-red-600 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h1 class="text-xl font-semibold text-gray-900">SACCO Members Portal</h1>
                            <p class="text-sm text-gray-500">Welcome, {{ $memberData->getFullNameAttribute() }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button wire:click="logout" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Account Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Savings Balance -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Savings</dt>
                                    <dd class="text-lg font-medium text-gray-900">TZS {{ number_format($savingsSummary['total_balance'] ?? 0) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shares Value -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Shares Value</dt>
                                    <dd class="text-lg font-medium text-gray-900">TZS {{ number_format($shareSummary['total_value'] ?? 0) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loan Balance -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Loan Balance</dt>
                                    <dd class="text-lg font-medium text-gray-900">TZS {{ number_format($loanSummary['total_balance'] ?? 0) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Accounts -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Accounts</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ count($accounts) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white shadow rounded-lg mb-8">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Transactions</h3>
                    @if(count($recentTransactions) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentTransactions as $transaction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction['created_at'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction['reference'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction['type'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS {{ number_format($transaction['amount']) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $transaction['status'] === 'COMPLETED' ? 'bg-green-100 text-green-800' : 
                                                   ($transaction['status'] === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ $transaction['status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No recent transactions found.</p>
                    @endif
                </div>
            </div>

            <!-- Account Details -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Your Accounts</h3>
                    @if(count($accounts) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($accounts as $account)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $account['account_type'] }}</h4>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $account['status'] === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $account['status'] }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mb-1">Account: {{ $account['account_number'] }}</p>
                                <p class="text-lg font-semibold text-gray-900">TZS {{ number_format($account['current_balance']) }}</p>
                                <p class="text-xs text-gray-500">{{ $account['product_name'] }}</p>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No accounts found.</p>
                    @endif
                </div>
            </div>
        </main>
    </div>
    @endif

    <!-- JavaScript for Password Toggle -->
    <script nonce="{{ csp_nonce() }}">
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
        }

        // Listen for Livewire events
        window.addEventListener('member-logged-in', event => {
            // Show success message
            console.log(event.detail.message);
        });

        window.addEventListener('member-registered', event => {
            // Show success message
            console.log(event.detail.message);
        });

        window.addEventListener('password-reset-sent', event => {
            // Show success message
            console.log(event.detail.message);
        });

        window.addEventListener('password-reset-success', event => {
            // Show success message
            console.log(event.detail.message);
        });

        window.addEventListener('member-logged-out', event => {
            // Show success message
            console.log(event.detail.message);
        });
    </script>
</div>
