<?php

if (!function_exists('csp_nonce')) {
    /**
     * Get the current CSP nonce for inline scripts
     *
     * Usage in Blade templates:
     * <script nonce="{{ csp_nonce() }}">
     *     // Your inline JavaScript
     * </script>
     *
     * @return string
     */
    function csp_nonce(): string
    {
        // Try to get nonce from view data first
        $nonce = View::shared('cspNonce');

        if ($nonce) {
            return $nonce;
        }

        // Try to get from request attributes
        if (request()->attributes->has('csp_nonce')) {
            return request()->attributes->get('csp_nonce');
        }

        // Fallback: generate a new nonce (should not happen in production)
        \Log::warning('CSP nonce not found, generating fallback nonce');
        return base64_encode(random_bytes(16));
    }
}

if (!function_exists('safe_html')) {
    /**
     * Sanitize HTML content using HTMLPurifier
     *
     * @param string $html The HTML to sanitize
     * @param string $config The HTMLPurifier config to use (default: 'default')
     * @return string Sanitized HTML
     */
    function safe_html(string $html, string $config = 'default'): string
    {
        if (function_exists('clean')) {
            return clean($html, $config);
        }

        // Fallback if HTMLPurifier is not available
        return htmlspecialchars($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('safe_json')) {
    /**
     * Safely encode data to JSON for use in HTML/JavaScript
     *
     * Prevents XSS by properly escaping special characters
     *
     * @param mixed $data The data to encode
     * @param int $options JSON encoding options
     * @return string
     */
    function safe_json($data, int $options = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT): string
    {
        return json_encode($data, $options);
    }
}

if (!function_exists('safe_url')) {
    /**
     * Sanitize a URL to prevent XSS via javascript: or data: protocols
     *
     * @param string $url The URL to sanitize
     * @param array $allowedProtocols Allowed URL protocols
     * @return string Sanitized URL or empty string if dangerous
     */
    function safe_url(string $url, array $allowedProtocols = ['http', 'https', 'mailto', 'tel']): string
    {
        $url = trim($url);

        if (empty($url)) {
            return '';
        }

        // Parse URL to get scheme
        $parsed = parse_url($url);

        // If no scheme, assume relative URL (safe)
        if (!isset($parsed['scheme'])) {
            // Check for attempts to bypass with // or ///
            if (preg_match('#^//+#', $url)) {
                return '';
            }
            return htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // Check if scheme is allowed
        $scheme = strtolower($parsed['scheme']);
        if (!in_array($scheme, $allowedProtocols)) {
            \Log::warning('Blocked dangerous URL scheme', [
                'url' => $url,
                'scheme' => $scheme,
                'allowed' => $allowedProtocols
            ]);
            return '';
        }

        return htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('safe_attribute')) {
    /**
     * Sanitize a string for safe use in HTML attributes
     *
     * @param string $value The value to sanitize
     * @return string Sanitized value
     */
    function safe_attribute(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('safe_js_string')) {
    /**
     * Sanitize a string for safe use in JavaScript string literals
     *
     * @param string $value The value to sanitize
     * @return string Sanitized value
     */
    function safe_js_string(string $value): string
    {
        // Escape backslashes and quotes
        $value = str_replace(['\\', "'", '"', "\n", "\r", "\t", '</'], ['\\\\', "\\'", '\\"', '\\n', '\\r', '\\t', '<\\/'], $value);
        return $value;
    }
}

if (!function_exists('strip_dangerous_tags')) {
    /**
     * Strip dangerous HTML tags that can execute JavaScript
     *
     * @param string $html The HTML to clean
     * @return string Cleaned HTML
     */
    function strip_dangerous_tags(string $html): string
    {
        $dangerousTags = [
            'script',
            'iframe',
            'object',
            'embed',
            'applet',
            'meta',
            'link',
            'style',
            'form',
            'input',
            'button',
            'textarea',
            'select'
        ];

        foreach ($dangerousTags as $tag) {
            // Remove opening and closing tags
            $html = preg_replace('#<' . $tag . '(\s+[^>]*)?>.*?</' . $tag . '>#is', '', $html);
            // Remove self-closing tags
            $html = preg_replace('#<' . $tag . '(\s+[^>]*)?/>#is', '', $html);
        }

        // Remove event handlers (onclick, onerror, etc.)
        $html = preg_replace('/\s*on\w+\s*=\s*["\'].*?["\']/i', '', $html);

        // Remove javascript: protocol
        $html = preg_replace('/javascript:/i', '', $html);

        // Remove data: protocol for images (can be used for XSS)
        $html = preg_replace('/data:text\/html/i', '', $html);

        return $html;
    }
}

if (!function_exists('is_safe_redirect')) {
    /**
     * Check if a redirect URL is safe (same origin)
     *
     * @param string $url The URL to check
     * @return bool True if safe, false otherwise
     */
    function is_safe_redirect(string $url): bool
    {
        // Empty URL is safe (will redirect to default)
        if (empty($url)) {
            return true;
        }

        // Relative URLs are safe
        if ($url[0] === '/') {
            // But not // (protocol-relative)
            if (isset($url[1]) && $url[1] === '/') {
                return false;
            }
            return true;
        }

        // Check if URL is from same domain
        $parsed = parse_url($url);

        if (!isset($parsed['host'])) {
            return true; // Relative URL
        }

        $currentHost = request()->getHost();
        $redirectHost = $parsed['host'];

        return $currentHost === $redirectHost;
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * Sanitize a filename to prevent directory traversal and other attacks
     *
     * @param string $filename The filename to sanitize
     * @return string Sanitized filename
     */
    function sanitize_filename(string $filename): string
    {
        // Remove path components
        $filename = basename($filename);

        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Remove leading dots (hidden files)
        $filename = ltrim($filename, '.');

        // Limit length
        if (strlen($filename) > 255) {
            $filename = substr($filename, 0, 255);
        }

        return $filename;
    }
}
