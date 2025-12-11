<x-guest-layout>
    <x-authentication-card>
        <x-validation-errors class="mb-6" />

        @session('status')
            <div class="mb-6 p-4 bg-gradient-to-r from-green-400/20 to-green-500/20 border-l-4 border-green-400 text-green-100 text-sm rounded-lg shadow-sm backdrop-blur-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ $value }}
                </div>
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div class="space-y-2">
                <label for="email" class="block text-sm font-bold text-white uppercase tracking-wide">Email Address</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors duration-200">
                        <svg class="h-6 w-6 text-white/40 group-focus-within:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" 
                        class="block w-full pl-14 pr-4 py-4 bg-white/10 border-2 border-white/20 rounded-xl focus:ring-4 focus:ring-red-500/50 focus:border-red-400 focus:bg-white/20 transition-all duration-200 text-white text-base font-medium placeholder-white/40 backdrop-blur-sm" 
                        placeholder="admin@example.com">
                </div>
            </div>

            <div class="space-y-2">
                <label for="password" class="block text-sm font-bold text-white uppercase tracking-wide">Password</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors duration-200">
                        <svg class="h-6 w-6 text-white/40 group-focus-within:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input id="password" type="password" name="password" required autocomplete="current-password" 
                        class="block w-full pl-14 pr-4 py-4 bg-white/10 border-2 border-white/20 rounded-xl focus:ring-4 focus:ring-red-500/50 focus:border-red-400 focus:bg-white/20 transition-all duration-200 text-white text-base font-medium placeholder-white/40 backdrop-blur-sm" 
                        placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <label for="remember_me" class="flex items-center cursor-pointer group">
                    <input id="remember_me" type="checkbox" name="remember" class="h-5 w-5 rounded-lg border-2 border-white/30 bg-white/10 text-red-600 focus:ring-4 focus:ring-red-500/50 transition-all duration-200">
                    <span class="ml-3 text-sm font-semibold text-white/80 group-hover:text-white transition-colors duration-200">Keep me signed in</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-bold text-red-400 hover:text-red-300 transition-all duration-200 hover:underline">
                        Forgot Password?
                    </a>
                @endif
            </div>

            <button type="submit" class="relative w-full py-4 px-6 rounded-xl text-white font-bold text-lg shadow-2xl hover:shadow-red-500/50 transform hover:-translate-y-1 hover:scale-105 active:translate-y-0 active:scale-100 transition-all duration-200 overflow-hidden group" style="background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);">
                <span class="relative z-10">Sign In to Portal</span>
                <div class="absolute inset-0 bg-gradient-to-r from-red-500 to-red-700 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
            </button>
        </form>

        <div class="mt-10 pt-8 border-t-2 border-white/10">
            <div class="flex items-center justify-center space-x-2 text-sm text-white/60">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">Secured by <span class="font-black text-white">CreditInfo</span> Encryption</span>
            </div>
        </div>
    </x-authentication-card>
</x-guest-layout>
