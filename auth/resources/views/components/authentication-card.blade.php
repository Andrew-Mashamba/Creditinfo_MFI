<div class="w-full sm:max-w-md z-10 relative">
    <div class="mb-8 text-center">
        <!-- CreditInfo Logo -->
        <div class="flex justify-center mb-6">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center shadow-2xl" style="background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);">
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
        </div>
        
        <h1 class="text-3xl font-bold text-white mb-2">CreditInfo</h1>
        <p class="text-red-200 text-lg">MFI Portal Access</p>
    </div>

    <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 p-8">
        {{ $slot }}
    </div>
</div>
