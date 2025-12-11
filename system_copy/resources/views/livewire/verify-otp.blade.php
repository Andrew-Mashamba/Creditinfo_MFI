<div class="flex flex-col justify-center">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-xl sm:rounded-lg sm:px-10">
            <!-- Header -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-extrabold text-gray-900">
                    Verify Your Account
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    We've sent a verification code to your email and phone
                </p>
            </div>

            <!-- Alerts -->
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('status') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if ($errorMessage)
                <div class="mb-4 rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ $errorMessage }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if ($successMessage)
                <div class="mb-4 rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ $successMessage }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- OTP Form -->
            <form wire:submit.prevent="submitOTP" class="space-y-6">
                <!-- OTP Input -->
                <div>
                    <label for="otp" class="block text-sm font-medium text-gray-700 mb-2">
                        Enter 6-digit code
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="otp"
                               wire:model.live="otp"
                               class="block w-full px-4 py-3 text-center text-2xl font-semibold tracking-widest border-2 border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-500 focus:ring-opacity-50 transition-colors"
                               maxlength="6"
                               pattern="[0-9]*"
                               inputmode="numeric"
                               autocomplete="one-time-code"
                               autofocus
                               x-on:keypress="if (!/^\d$/.test($event.key)) $event.preventDefault()"
                               placeholder="000000">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Countdown Timer -->
                <div class="relative pt-1">
                    <div class="flex mb-2 items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-primary-600 bg-primary-200">
                                Time Remaining
                            </span>
                        </div>
                        <div class="text-right">
                            <span id="countdownDisplay" class="text-xs font-semibold inline-block text-primary-600">
                                5:00
                            </span>
                        </div>
                    </div>
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-primary-200">
                        <div id="countdownProgress" class="transition-all duration-1000 ease-out shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-primary-500"
                             style="width: 100%">
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="space-y-4">
                    <button type="button"
                            id="resendButton"
                            class="w-full flex justify-center py-3 px-4 border border-primary-300 rounded-md shadow-sm text-sm font-medium text-primary-700 bg-white hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                            wire:click="resendOTP"
                            wire:loading.attr="disabled"
                            wire:target="resendOTP">
                        <span wire:loading.remove wire:target="resendOTP">Resend Code</span>
                        <span wire:loading wire:target="resendOTP">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-primary-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Sending...
                        </span>
                    </button>

                    <button type="button"
                            class="w-full flex justify-center py-3 px-4 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                            wire:click="logout">
                        Sign Out
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
    document.addEventListener('livewire:load', function () {
        let countdown = 300; // 5 minutes in seconds
        let timer = null;
        const countdownDisplay = document.getElementById('countdownDisplay');
        const countdownProgress = document.getElementById('countdownProgress');
        const resendButton = document.getElementById('resendButton');
        const otpInput = document.getElementById('otp');

        function updateCountdown() {
            if (countdown <= 0) {
                clearInterval(timer);
                timer = null;
                // Enable resend button when countdown reaches zero
                resendButton.disabled = false;
                // Trigger logout after 5 seconds
                setTimeout(() => {
                    Livewire.emit('logout');
                }, 5000);
                return;
            }

            const minutes = Math.floor(countdown / 60);
            const seconds = countdown % 60;
            countdownDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            countdownProgress.style.width = `${(countdown / 300) * 100}%`;
            countdown--;
        }

        function startCountdown() {
            if (timer) {
                clearInterval(timer);
            }
            countdown = 300;
            // Allow resend button to be clicked anytime (remove the disability)
            resendButton.disabled = false;
            timer = setInterval(updateCountdown, 1000);
            updateCountdown(); // Initial update
        }

        // Start initial countdown
        startCountdown();

        // Handle OTP validation response
        Livewire.on('otpValidated', (response) => {
            if (!response.success) {
                // Clear input and focus
                otpInput.value = '';
                otpInput.focus();
            }
        });

        // Reset countdown when resending OTP
        Livewire.on('otpResent', () => {
            startCountdown();
        });

        // Cleanup on component destroy
        Livewire.on('otpComponentDestroyed', () => {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        });

        // Handle component updates
        Livewire.on('otpComponentUpdated', () => {
            if (!timer) {
                startCountdown();
            }
        });
    });
</script>
@endpush
