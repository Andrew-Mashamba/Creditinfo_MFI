<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Validate API Headers Middleware
 *
 * Validates Content-Type and Accept headers for API requests
 *
 * @package App\Http\Middleware
 * @author MFI Management System Security Team
 * @date 2025-10-16
 */
class ValidateApiHeaders
{
    /**
     * Allowed Content-Type values for JSON APIs
     *
     * @var array
     */
    protected $allowedContentTypes = [
        'application/json',
        'application/json; charset=utf-8',
        'application/json; charset=UTF-8',
    ];

    /**
     * Allowed Accept header values
     *
     * @var array
     */
    protected $allowedAcceptTypes = [
        'application/json',
        '*/*',
        'application/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  bool  $strictContentType  Enforce Content-Type validation
     * @param  bool  $strictAccept  Enforce Accept header validation
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $strictContentType = 'true', $strictAccept = 'false')
    {
        $errors = [];

        // Convert string parameters to boolean
        $strictContentType = filter_var($strictContentType, FILTER_VALIDATE_BOOLEAN);
        $strictAccept = filter_var($strictAccept, FILTER_VALIDATE_BOOLEAN);

        // Validate Content-Type for requests with body (POST, PUT, PATCH)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $contentType = $request->header('Content-Type');

            if (!$contentType) {
                if ($strictContentType) {
                    $errors[] = 'Content-Type header is required';
                }
            } else {
                // Remove parameters for comparison (e.g., "; charset=utf-8")
                $baseContentType = strtolower(trim(explode(';', $contentType)[0]));

                if (!in_array($baseContentType, array_map('strtolower', $this->allowedContentTypes)) &&
                    !str_starts_with($baseContentType, 'application/json')) {
                    $errors[] = sprintf(
                        'Invalid Content-Type header. Expected application/json, got %s',
                        $baseContentType
                    );
                }
            }
        }

        // Validate Accept header
        $accept = $request->header('Accept');

        if ($strictAccept) {
            if (!$accept) {
                $errors[] = 'Accept header is required';
            } else {
                $acceptTypes = array_map('trim', explode(',', strtolower($accept)));
                $validAccept = false;

                foreach ($acceptTypes as $acceptType) {
                    // Remove quality parameters (e.g., q=0.9)
                    $cleanAcceptType = trim(explode(';', $acceptType)[0]);

                    if (in_array($cleanAcceptType, array_map('strtolower', $this->allowedAcceptTypes)) ||
                        str_starts_with($cleanAcceptType, 'application/json')) {
                        $validAccept = true;
                        break;
                    }
                }

                if (!$validAccept) {
                    $errors[] = sprintf(
                        'Invalid Accept header. Expected application/json, got %s',
                        $accept
                    );
                }
            }
        }

        // Check for dangerous Content-Type values
        if ($contentType = $request->header('Content-Type')) {
            $dangerousTypes = [
                'text/html',
                'text/javascript',
                'application/x-www-form-urlencoded', // Should use JSON
            ];

            foreach ($dangerousTypes as $dangerousType) {
                if (str_contains(strtolower($contentType), $dangerousType)) {
                    Log::warning('Potentially dangerous Content-Type in API request', [
                        'content_type' => $contentType,
                        'ip' => $request->ip(),
                        'endpoint' => $request->path(),
                    ]);
                }
            }
        }

        // Return error response if validation failed
        if (!empty($errors)) {
            Log::warning('API Header Validation Failed', [
                'errors' => $errors,
                'content_type' => $request->header('Content-Type'),
                'accept' => $request->header('Accept'),
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid API headers',
                'errors' => $errors,
                'error_code' => 'INVALID_HEADERS',
            ], 400);
        }

        // Set default response content type
        $response = $next($request);

        // Ensure JSON response has correct Content-Type
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $response->header('Content-Type', 'application/json; charset=utf-8');
        }

        return $response;
    }
}
