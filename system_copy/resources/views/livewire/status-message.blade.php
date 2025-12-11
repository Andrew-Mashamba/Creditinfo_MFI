@push('styles')
<style nonce="{{ csp_nonce() }}">
    .status-container {
        @apply min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8;
    }
    .status-card {
        @apply max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-lg;
    }
    .status-icon {
        @apply mx-auto h-16 w-16 text-red-500;
    }
    .status-title {
        @apply mt-6 text-center text-3xl font-extrabold text-gray-900;
    }
    .status-message {
        @apply mt-2 text-center text-sm text-gray-600;
    }
    .status-action {
        @apply mt-8 text-center text-sm text-gray-600;
    }
</style>
@endpush

<div class="status-container">
    <div class="status-card">
        <div class="text-center">
            <svg class="status-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h2 class="status-title">
                @if($status === 'PENDING')
                    Account Pending
                @elseif($status === 'BLOCKED')
                    Account Blocked
                @else
                    Account Deleted
                @endif
            </h2>
            <p class="status-message">
                @if($status === 'PENDING')
                    Your account is currently pending approval. Please contact your administrator for assistance.
                @elseif($status === 'BLOCKED')
                    Your account has been blocked. Please contact your administrator to resolve this issue.
                @else
                    Your account has been deleted. Please contact your administrator if you believe this is an error.
                @endif
            </p>
            <div class="status-action">
                <p>Please contact your system administrator at:</p>
                <a href="mailto:support@nbc.co.ke" class="text-primary-600 hover:text-primary-500">
                    support@nbc.co.ke
                </a>
            </div>
        </div>
    </div>
</div> 