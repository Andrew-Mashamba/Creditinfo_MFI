{{-- CSRF Token Auto-Refresh Component --}}
{{-- Include this in your main layout to automatically refresh CSRF tokens --}}
{{-- SECURITY: Prevents "419 Page Expired" errors on long-lived sessions --}}

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    /**
     * CSRF Token Auto-Refresh
     * Automatically updates CSRF tokens every 30 minutes to prevent expiration
     * during long user sessions with forms open
     */
    (function() {
        'use strict';

        // Refresh CSRF token every 30 minutes (1800000 ms)
        const REFRESH_INTERVAL = 30 * 60 * 1000;

        function refreshCsrfToken() {
            fetch('/csrf-token-refresh', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.token) {
                    // Update meta tag
                    const metaTag = document.querySelector('meta[name="csrf-token"]');
                    if (metaTag) {
                        metaTag.setAttribute('content', data.token);
                    }

                    // Update all form tokens
                    document.querySelectorAll('input[name="_token"]').forEach(input => {
                        input.value = data.token;
                    });

                    // Update Axios defaults if Axios is available
                    if (typeof axios !== 'undefined') {
                        axios.defaults.headers.common['X-CSRF-TOKEN'] = data.token;
                    }

                    // Update jQuery AJAX defaults if jQuery is available
                    if (typeof $ !== 'undefined') {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': data.token
                            }
                        });
                    }

                    console.log('CSRF token refreshed successfully');
                }
            })
            .catch(error => {
                console.error('CSRF token refresh failed:', error);
            });
        }

        // Initial token setup for AJAX
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            // Set up Axios if available
            if (typeof axios !== 'undefined') {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
            }

            // Set up jQuery if available
            if (typeof $ !== 'undefined') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': token.content
                    }
                });
            }
        }

        // Start automatic refresh
        setInterval(refreshCsrfToken, REFRESH_INTERVAL);

        // Also refresh on visibility change (when user returns to tab)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                refreshCsrfToken();
            }
        });
    })();
</script>
