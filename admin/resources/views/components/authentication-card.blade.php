<div class="min-h-screen flex relative overflow-hidden" style="background: linear-gradient(135deg, #1f1f1f 0%, #2c1810 50%, #1f1f1f 100%);">
    <!-- Animated gradient overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-black via-red-900 to-black opacity-60"></div>
    
    <!-- Decorative elements -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-red-600 to-red-900 opacity-20 rounded-full blur-3xl transform translate-x-48 -translate-y-48"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-gradient-to-tr from-red-800 to-red-600 opacity-20 rounded-full blur-3xl transform -translate-x-48 translate-y-48"></div>
    
    <div class="flex-1 relative z-10 flex items-center justify-center p-12">
        <div class="max-w-2xl w-full">
            <!-- Main branding and login card -->
            <div class="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-2xl rounded-3xl p-12 shadow-2xl border border-white/20">
                <div class="flex items-center mb-8">
                    <div class="w-20 h-20 rounded-2xl flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);">
                        <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <div class="ml-6">
                        <h1 class="text-6xl font-black text-white mb-2 tracking-tight">CreditInfo</h1>
                        <div class="h-2 w-full rounded-full mb-3" style="background: linear-gradient(90deg, #DC2626 0%, #B91C1C 50%, transparent 100%);"></div>
                    </div>
                </div>
                <p class="text-3xl font-bold text-white tracking-wide mb-6 text-center">MFI Admin Portal</p>
                <p class="text-white/80 text-lg leading-relaxed mb-10 text-center">
                    Empowering microfinance institutions with cutting-edge management solutions. 
                    Secure, reliable, and built for excellence.
                </p>
                
                <!-- Login Form -->
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
