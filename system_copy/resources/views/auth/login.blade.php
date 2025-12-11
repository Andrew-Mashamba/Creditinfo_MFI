<x-authentication-layout>
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-white mb-2">
        Saccos Management System
        </h2>
        <p class="text-gray-500">
            Sign in to access your account
        </p>
    </div>

    <!-- Alert Messages -->
    @if (session('status'))
        <div class="mb-6 p-4 glass-dark rounded-xl border border-green-400 border-opacity-30">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-100">
                        {{ session('status') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 glass-dark rounded-xl border border-red-400 border-opacity-30">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-100">
                        {{ session('error') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Login Form -->
    <form class="space-y-6" action="{{ route('login') }}" method="POST">
        @csrf

        <!-- Email Input -->
        <div>
            <label for="email" class="block text-sm font-medium text-white mb-2">
                Email Address
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-blue-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                </div>
                <input id="email" name="email" type="email" autocomplete="email" required
                    class="form-input block w-full pl-12 pr-4 py-3 text-white placeholder-blue-200 rounded-xl transition-all duration-300 @error('email') border-red-400 @enderror"
                    placeholder="Enter your email address"
                    value="{{ old('email', Session::get('email')) }}">
            </div>
            @error('email')
                <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Input -->
        <div>
            <label for="password" class="block text-sm font-medium text-white mb-2">
                Password
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-blue-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                    class="form-input block w-full pl-12 pr-12 py-3 text-white placeholder-blue-200 rounded-xl transition-all duration-300 @error('password') border-red-400 @enderror"
                    placeholder="Enter your password">
                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                    <svg class="h-5 w-5 text-blue-200 hover:text-white transition-colors duration-200" id="eyeIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember_me" name="remember" type="checkbox"
                    class="h-4 w-4 text-brand-500 focus:ring-brand-500 border-blue-300 rounded bg-transparent">
                <label for="remember_me" class="ml-2 block text-sm text-white">
                    Remember me
                </label>
            </div>

            @if (Route::has('password.request'))
                <div class="text-sm">
                    <a href="{{ route('password.request') }}" class="font-medium text-blue-200 hover:text-white transition-colors duration-200">
                        Forgot password?
                    </a>
                </div>
            @endif
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit" id="submitBtn"
                class="btn-primary w-full flex justify-center items-center py-3 px-4 rounded-xl text-white font-medium text-sm transition-all duration-300">
                <span id="btnText">Sign In</span>
                <svg id="loadingIcon" class="hidden animate-spin ml-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </div>
    </form>

    <!-- Security Notice -->
    <div class="mt-8">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-blue-200 border-opacity-30"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-4 glass-dark rounded-full text-blue-200">
                    Security Notice
                </span>
            </div>
        </div>
        <div class="mt-6 text-center">
            <div class="flex items-center justify-center space-x-2 p-4 glass-dark rounded-xl">
                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <span class="text-gray-500 text-sm">All activities are monitored. Unauthorized access is prohibited.</span>
            </div>
        </div>
    </div>

    <!-- Enhanced Security Features Script -->
    <script nonce="{{ csp_nonce() }}">
        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Clear existing paths
            while (eyeIcon.firstChild) {
                eyeIcon.removeChild(eyeIcon.firstChild);
            }

            if (type === 'text') {
                // Eye-off icon (password visible)
                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('stroke-linecap', 'round');
                path.setAttribute('stroke-linejoin', 'round');
                path.setAttribute('stroke-width', '2');
                path.setAttribute('d', 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21');
                eyeIcon.appendChild(path);
            } else {
                // Eye icon (password hidden)
                const path1 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path1.setAttribute('stroke-linecap', 'round');
                path1.setAttribute('stroke-linejoin', 'round');
                path1.setAttribute('stroke-width', '2');
                path1.setAttribute('d', 'M15 12a3 3 0 11-6 0 3 3 0 016 0z');
                const path2 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path2.setAttribute('stroke-linecap', 'round');
                path2.setAttribute('stroke-linejoin', 'round');
                path2.setAttribute('stroke-width', '2');
                path2.setAttribute('d', 'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z');
                eyeIcon.appendChild(path1);
                eyeIcon.appendChild(path2);
            }
        });

        // Password strength indicator
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            if (password.length >= 8) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[^a-zA-Z0-9]+/)) strength++;

            // Visual feedback for password strength
            const strengthColors = ['', 'bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];
            const strengthTexts = ['', 'Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
            
            // You can add a strength indicator bar here if needed
        });

        // Form submission handling
        const form = document.querySelector('form');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const loadingIcon = document.getElementById('loadingIcon');

        form.addEventListener('submit', function(e) {
            // Prevent multiple submissions
            submitBtn.disabled = true;
            btnText.textContent = 'Signing in...';
            loadingIcon.classList.remove('hidden');
            
            // Add shimmer effect
            submitBtn.classList.add('shimmer');
        });

        // Input focus effects
        const inputs = document.querySelectorAll('input[type="email"], input[type="password"]');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('ring-2', 'ring-blue-400', 'ring-opacity-50');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('ring-2', 'ring-blue-400', 'ring-opacity-50');
            });
        });

        // Add floating animation to form elements
        const formElements = document.querySelectorAll('form > div');
        formElements.forEach((element, index) => {
            element.style.animationDelay = `${index * 0.1}s`;
            element.classList.add('animate-slide-up');
        });
    </script>
</x-authentication-layout>
