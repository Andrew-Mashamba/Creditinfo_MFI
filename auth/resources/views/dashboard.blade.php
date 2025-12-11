@php
    // Check if the user has an MFI portal to redirect to
    $user = auth()->user();
    if ($user && $user->port) {
        // Redirect to the MFI portal
        $redirectUrl = $user->getMfiPortalUrl();
        header("Location: $redirectUrl");
        exit;
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('MFI Portal Access') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Redirecting to MFI Portal...</h3>
                <p>If you are not redirected automatically, <a href="{{ $user ? $user->getMfiPortalUrl() : '#' }}" class="text-blue-600 hover:underline">click here</a>.</p>
                
                @if($user && $user->port)
                <script>
                    window.location.href = "{{ $user->getMfiPortalUrl() }}";
                </script>
                @else
                <p class="mt-4 text-red-600">No MFI portal assigned to your account. Please contact the administrator.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
