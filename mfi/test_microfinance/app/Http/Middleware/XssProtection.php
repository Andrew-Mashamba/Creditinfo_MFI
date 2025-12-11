<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * XSS Protection Middleware
 *
 * Sanitizes request inputs to prevent XSS attacks
 * Cleans dangerous characters and patterns from user input
 *
 * @package App\Http\Middleware
 * @author MFI Management System Security Team
 * @date 2025-10-16
 */
class XssProtection
{
    /**
     * Dangerous strings that should be removed or escaped
     *
     * @var array
     */
    protected $dangerousStrings = [
        'javascript:',
        'vbscript:',
        'data:text/html',
        '<script',
        '</script>',
        '<iframe',
        '</iframe>',
        '<object',
        '</object>',
        '<embed',
        '</embed>',
        '<applet',
        '</applet>',
        'onload=',
        'onerror=',
        'onclick=',
        'onmouseover=',
        'onfocus=',
        'onblur=',
    ];

    /**
     * Fields that should be excluded from XSS cleaning
     * (e.g., rich text editors with HTMLPurifier)
     *
     * @var array
     */
    protected $excludedFields = [
        'content',
        'description',
        'body',
        'message',
        'signature',
        'template',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip sanitization for:
        // 1. API routes (use different validation)
        // 2. Livewire requests (have encrypted/signed payloads that must not be modified)
        if (!$request->is('api/*') && !$this->isLivewireRequest($request)) {
            // Sanitize all input data
            $input = $request->all();
            $sanitized = $this->sanitizeInput($input);
            $request->merge($sanitized);
        }

        return $next($request);
    }

    /**
     * Check if the request is a Livewire request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isLivewireRequest(Request $request): bool
    {
        // Check for Livewire-specific indicators
        return $request->hasHeader('X-Livewire') ||
               $request->is('livewire/*') ||
               $request->has('components') ||
               $request->has('serverMemo');
    }

    /**
     * Recursively sanitize input data
     *
     * @param mixed $input
     * @param string $key
     * @return mixed
     */
    protected function sanitizeInput($input, $key = '')
    {
        if (is_array($input)) {
            $sanitized = [];
            foreach ($input as $itemKey => $value) {
                $sanitized[$itemKey] = $this->sanitizeInput($value, $itemKey);
            }
            return $sanitized;
        }

        if (is_string($input)) {
            // Skip excluded fields (will be sanitized by HTMLPurifier)
            if (in_array($key, $this->excludedFields)) {
                return $input;
            }

            return $this->sanitizeString($input);
        }

        return $input;
    }

    /**
     * Sanitize a string value
     *
     * @param string $input
     * @return string
     */
    protected function sanitizeString(string $input): string
    {
        // Trim whitespace
        $input = trim($input);

        // Remove null bytes
        $input = str_replace(chr(0), '', $input);

        // Remove dangerous strings (case-insensitive)
        foreach ($this->dangerousStrings as $dangerous) {
            $input = preg_replace('/' . preg_quote($dangerous, '/') . '/i', '', $input);
        }

        // Remove event handlers with regex
        $input = preg_replace('/\s*on\w+\s*=\s*["\']?[^"\']*["\']?/i', '', $input);

        // Clean up multiple spaces
        $input = preg_replace('/\s+/', ' ', $input);

        // Remove HTML comments
        $input = preg_replace('/<!--.*?-->/s', '', $input);

        return $input;
    }
}
