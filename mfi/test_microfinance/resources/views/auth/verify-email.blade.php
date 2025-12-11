<x-authentication-layout>
    <h1 class="text-3xl text-slate-800 font-bold mb-6">{{ __('Verify your Email') }} ✨</h1>
    <div>
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <div>
                <x-jet-button type="submit">
                    {{ __('Resend Verification Email') }}
                </x-jet-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <div class="ml-1">
                <button type="submit" class="text-sm underline hover:no-underline">
                    {{ __('Log Out') }}
                </button>
            </div>
        </form>
    </div>
</x-authentication-layout>
