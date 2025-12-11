<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Validate File Upload Middleware
 *
 * Comprehensive file upload security:
 * - File size validation
 * - Filename sanitization
 * - MIME type validation
 * - Extension whitelist
 * - Path traversal prevention
 * - Executable file detection
 *
 * @package App\Http\Middleware
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class ValidateFileUpload
{
    /**
     * Maximum file size in bytes (20MB default)
     *
     * @var int
     */
    protected $maxFileSize = 20971520; // 20MB

    /**
     * Allowed file extensions
     *
     * @var array
     */
    protected $allowedExtensions = [
        // Documents
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'txt', 'rtf', 'odt', 'ods', 'odp', 'csv',

        // Images
        'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg',

        // Archives (if needed)
        'zip', 'rar', '7z', 'tar', 'gz',
    ];

    /**
     * Dangerous file extensions (always blocked)
     *
     * @var array
     */
    protected $dangerousExtensions = [
        'php', 'php3', 'php4', 'php5', 'phtml', 'phar',
        'exe', 'com', 'bat', 'cmd', 'sh', 'bash',
        'js', 'jar', 'app', 'dmg', 'vbs', 'wsf',
        'asp', 'aspx', 'jsp', 'cgi', 'pl', 'py',
        'rb', 'ps1', 'msi', 'scr', 'dll', 'so',
    ];

    /**
     * Allowed MIME types
     *
     * @var array
     */
    protected $allowedMimeTypes = [
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'text/csv',
        'application/rtf',

        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/bmp',
        'image/webp',
        'image/svg+xml',

        // Archives
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
        'application/x-tar',
        'application/gzip',
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
        // Check if request has files
        if (!$request->hasFile()) {
            return $next($request);
        }

        // Get all uploaded files
        $files = $this->getAllUploadedFiles($request);

        // Validate each file
        foreach ($files as $fieldName => $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $validation = $this->validateFile($file, $fieldName);

            if (!$validation['valid']) {
                Log::warning('File upload validation failed', [
                    'user_id' => auth()->id(),
                    'field' => $fieldName,
                    'reason' => $validation['reason'],
                    'filename' => $file->getClientOriginalName(),
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'error' => 'File upload validation failed',
                    'message' => $validation['reason'],
                    'field' => $fieldName,
                ], 422);
            }

            // Sanitize filename and add to request
            $sanitizedName = $this->sanitizeFilename($file->getClientOriginalName());
            $request->attributes->set("sanitized_filename_{$fieldName}", $sanitizedName);
        }

        return $next($request);
    }

    /**
     * Get all uploaded files from request
     *
     * @param  Request  $request
     * @return array
     */
    protected function getAllUploadedFiles(Request $request): array
    {
        $files = [];

        foreach ($request->files->all() as $key => $value) {
            if (is_array($value)) {
                // Handle multiple file uploads
                foreach ($value as $file) {
                    if ($file && is_object($file)) {
                        $files[$key] = $file;
                    }
                }
            } else {
                if ($value && is_object($value)) {
                    $files[$key] = $value;
                }
            }
        }

        return $files;
    }

    /**
     * Validate uploaded file
     *
     * @param  mixed  $file
     * @param  string  $fieldName
     * @return array
     */
    protected function validateFile($file, string $fieldName): array
    {
        // 1. Check file size
        if ($file->getSize() > $this->maxFileSize) {
            return [
                'valid' => false,
                'reason' => 'File size exceeds maximum allowed size of ' . ($this->maxFileSize / 1048576) . 'MB',
            ];
        }

        // 2. Get file extension
        $extension = strtolower($file->getClientOriginalExtension());

        // 3. Check for dangerous extensions
        if (in_array($extension, $this->dangerousExtensions)) {
            return [
                'valid' => false,
                'reason' => "File extension '{$extension}' is not allowed for security reasons",
            ];
        }

        // 4. Check against whitelist
        if (!in_array($extension, $this->allowedExtensions)) {
            return [
                'valid' => false,
                'reason' => "File extension '{$extension}' is not allowed. Allowed: " . implode(', ', $this->allowedExtensions),
            ];
        }

        // 5. Validate MIME type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            return [
                'valid' => false,
                'reason' => "File type '{$mimeType}' is not allowed",
            ];
        }

        // 6. Check for double extensions (e.g., file.php.jpg)
        $originalName = $file->getClientOriginalName();
        if ($this->hasDoubleExtension($originalName)) {
            return [
                'valid' => false,
                'reason' => 'Files with multiple extensions are not allowed',
            ];
        }

        // 7. Check for PHP in file content (basic check)
        if ($this->containsPhpCode($file)) {
            return [
                'valid' => false,
                'reason' => 'File contains potentially malicious code',
            ];
        }

        // 8. Validate filename characters
        if (!$this->hasValidFilenameCharacters($originalName)) {
            return [
                'valid' => false,
                'reason' => 'Filename contains invalid characters',
            ];
        }

        return ['valid' => true];
    }

    /**
     * Sanitize filename
     *
     * @param  string  $filename
     * @return string
     */
    protected function sanitizeFilename(string $filename): string
    {
        // Get file extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);

        // Remove any dangerous characters
        $basename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $basename);

        // Limit length
        $basename = substr($basename, 0, 100);

        // Add timestamp to prevent collisions
        $timestamp = time();

        // Generate random string
        $random = Str::random(8);

        return "{$basename}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Check if filename has double extension
     *
     * @param  string  $filename
     * @return bool
     */
    protected function hasDoubleExtension(string $filename): bool
    {
        $parts = explode('.', $filename);

        if (count($parts) <= 2) {
            return false;
        }

        // Check if any part before the last one is a dangerous extension
        for ($i = 0; $i < count($parts) - 1; $i++) {
            if (in_array(strtolower($parts[$i]), $this->dangerousExtensions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if file contains PHP code
     *
     * @param  mixed  $file
     * @return bool
     */
    protected function containsPhpCode($file): bool
    {
        try {
            $content = file_get_contents($file->getRealPath());

            // Check for PHP opening tags
            $phpPatterns = [
                '/<\?php/i',
                '/<\?=/i',
                '/<\?/i',
                '/<script\s+language\s*=\s*["\']php["\']/i',
            ];

            foreach ($phpPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error checking file content', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Validate filename characters
     *
     * @param  string  $filename
     * @return bool
     */
    protected function hasValidFilenameCharacters(string $filename): bool
    {
        // Check for path traversal attempts
        if (strpos($filename, '..') !== false) {
            return false;
        }

        if (strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            return false;
        }

        // Check for null bytes
        if (strpos($filename, "\0") !== false) {
            return false;
        }

        return true;
    }
}
