<?php

namespace App\Services\Security;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Secure File Upload Service
 *
 * Provides secure file upload handling with MIME type verification,
 * file extension validation, and content-based security checks.
 *
 * @package App\Services\Security
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class SecureFileUploadService
{
    /**
     * Allowed MIME types and their extensions
     */
    const ALLOWED_TYPES = [
        // Documents
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'csv' => ['text/csv', 'text/plain', 'application/csv'],

        // Images
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],

        // Text
        'txt' => ['text/plain'],
        'rtf' => ['application/rtf', 'text/rtf'],
    ];

    /**
     * Default maximum file size in kilobytes
     */
    const DEFAULT_MAX_SIZE_KB = 10240; // 10MB

    /**
     * Input validation service instance
     *
     * @var InputValidationService
     */
    protected $validator;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->validator = new InputValidationService();
    }

    /**
     * Upload file securely with comprehensive validation
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param array $options [
     *     'allowed_extensions' => array,
     *     'max_size_kb' => int,
     *     'disk' => string,
     *     'visibility' => string,
     *     'generate_unique_name' => bool
     * ]
     * @return array ['success' => bool, 'path' => string|null, 'filename' => string|null, 'error' => string|null]
     */
    public function upload(UploadedFile $file, string $directory, array $options = []): array
    {
        // Step 1: Validate file upload
        $validation = $this->validateFile($file, $options);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'path' => null,
                'filename' => null,
                'error' => $validation['error']
            ];
        }

        // Step 2: Generate secure filename
        $filename = $options['generate_unique_name'] ?? true
            ? $this->generateSecureFilename($file)
            : $this->sanitizeFilename($file->getClientOriginalName());

        // Step 3: Store file
        try {
            $disk = $options['disk'] ?? 'public';
            $visibility = $options['visibility'] ?? 'private';

            $path = $file->storeAs(
                $directory,
                $filename,
                ['disk' => $disk, 'visibility' => $visibility]
            );

            if (!$path) {
                return [
                    'success' => false,
                    'path' => null,
                    'filename' => null,
                    'error' => 'Failed to store file'
                ];
            }

            return [
                'success' => true,
                'path' => $path,
                'filename' => $filename,
                'error' => null
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'path' => null,
                'filename' => null,
                'error' => 'File upload failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate uploaded file
     *
     * @param UploadedFile $file
     * @param array $options
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public function validateFile(UploadedFile $file, array $options = []): array
    {
        // Check if file is valid
        if (!$file->isValid()) {
            return [
                'valid' => false,
                'error' => 'Invalid file upload. Error: ' . $file->getErrorMessage()
            ];
        }

        // Validate file size
        $maxSizeKb = $options['max_size_kb'] ?? self::DEFAULT_MAX_SIZE_KB;
        $fileSizeKb = $file->getSize() / 1024;

        if ($fileSizeKb > $maxSizeKb) {
            return [
                'valid' => false,
                'error' => sprintf(
                    'File size (%.2f MB) exceeds maximum allowed size (%.2f MB)',
                    $fileSizeKb / 1024,
                    $maxSizeKb / 1024
                )
            ];
        }

        // Validate extension
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = $options['allowed_extensions'] ?? array_keys(self::ALLOWED_TYPES);

        if (!in_array($extension, $allowedExtensions)) {
            return [
                'valid' => false,
                'error' => sprintf(
                    'File extension ".%s" is not allowed. Allowed: %s',
                    $extension,
                    implode(', ', $allowedExtensions)
                )
            ];
        }

        // Validate MIME type (content-based check)
        $mimeValidation = $this->validateMimeType($file, $extension);
        if (!$mimeValidation['valid']) {
            return $mimeValidation;
        }

        // Validate filename for dangerous patterns
        $filename = $file->getClientOriginalName();
        if ($this->hasDisallowedFilename($filename)) {
            return [
                'valid' => false,
                'error' => 'Filename contains disallowed characters or patterns'
            ];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Validate MIME type matches extension
     *
     * @param UploadedFile $file
     * @param string $extension
     * @return array ['valid' => bool, 'error' => string|null]
     */
    protected function validateMimeType(UploadedFile $file, string $extension): array
    {
        if (!isset(self::ALLOWED_TYPES[$extension])) {
            return [
                'valid' => false,
                'error' => "Extension '.{$extension}' is not in allowed types list"
            ];
        }

        // Get actual MIME type from file content
        $actualMime = $file->getMimeType();
        $allowedMimes = self::ALLOWED_TYPES[$extension];

        if (!in_array($actualMime, $allowedMimes)) {
            // Also check using finfo (more reliable)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $contentMime = finfo_file($finfo, $file->getRealPath());
            finfo_close($finfo);

            if (!in_array($contentMime, $allowedMimes)) {
                return [
                    'valid' => false,
                    'error' => sprintf(
                        'File MIME type "%s" does not match extension ".%s". Expected: %s',
                        $actualMime,
                        $extension,
                        implode(' or ', $allowedMimes)
                    )
                ];
            }
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Generate secure filename
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function generateSecureFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $uuid = Str::uuid()->toString();

        return $uuid . '.' . $extension;
    }

    /**
     * Sanitize filename for safe storage
     *
     * @param string $filename
     * @return string
     */
    protected function sanitizeFilename(string $filename): string
    {
        return $this->validator->sanitizeFilename($filename);
    }

    /**
     * Check if filename contains disallowed patterns
     *
     * @param string $filename
     * @return bool
     */
    protected function hasDisallowedFilename(string $filename): bool
    {
        // Check for path traversal attempts
        if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            return true;
        }

        // Check for null bytes
        if (str_contains($filename, "\0")) {
            return true;
        }

        // Check for executable extensions (double extension trick)
        $dangerousExtensions = [
            'php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'pht',
            'exe', 'dll', 'sh', 'bash', 'bat', 'cmd', 'com',
            'js', 'jar', 'jsp', 'asp', 'aspx', 'cgi', 'pl'
        ];

        foreach ($dangerousExtensions as $ext) {
            if (preg_match('/\.' . $ext . '($|\.)/i', $filename)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Upload multiple files
     *
     * @param array $files Array of UploadedFile objects
     * @param string $directory
     * @param array $options
     * @return array ['successful' => array, 'failed' => array]
     */
    public function uploadMultiple(array $files, string $directory, array $options = []): array
    {
        $successful = [];
        $failed = [];

        foreach ($files as $index => $file) {
            if (!$file instanceof UploadedFile) {
                $failed[$index] = [
                    'error' => 'Invalid file object'
                ];
                continue;
            }

            $result = $this->upload($file, $directory, $options);

            if ($result['success']) {
                $successful[$index] = $result;
            } else {
                $failed[$index] = $result;
            }
        }

        return [
            'successful' => $successful,
            'failed' => $failed
        ];
    }

    /**
     * Delete uploaded file
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public function delete(string $path, string $disk = 'public'): bool
    {
        try {
            return Storage::disk($disk)->delete($path);
        } catch (\Exception $e) {
            \Log::error('File deletion failed', [
                'path' => $path,
                'disk' => $disk,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if file exists
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public function exists(string $path, string $disk = 'public'): bool
    {
        try {
            return Storage::disk($disk)->exists($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get file URL for public files
     *
     * @param string $path
     * @param string $disk
     * @return string|null
     */
    public function getUrl(string $path, string $disk = 'public'): ?string
    {
        try {
            if (!$this->exists($path, $disk)) {
                return null;
            }

            return Storage::disk($disk)->url($path);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get temporary URL for private files
     *
     * @param string $path
     * @param int $expirationMinutes
     * @param string $disk
     * @return string|null
     */
    public function getTemporaryUrl(string $path, int $expirationMinutes = 60, string $disk = 'private'): ?string
    {
        try {
            if (!$this->exists($path, $disk)) {
                return null;
            }

            return Storage::disk($disk)->temporaryUrl(
                $path,
                now()->addMinutes($expirationMinutes)
            );
        } catch (\Exception $e) {
            \Log::error('Failed to generate temporary URL', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get file size in bytes
     *
     * @param string $path
     * @param string $disk
     * @return int|null
     */
    public function getSize(string $path, string $disk = 'public'): ?int
    {
        try {
            return Storage::disk($disk)->size($path);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get human-readable file size
     *
     * @param string $path
     * @param string $disk
     * @return string|null
     */
    public function getHumanSize(string $path, string $disk = 'public'): ?string
    {
        $bytes = $this->getSize($path, $disk);

        if ($bytes === null) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

        return number_format($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }

    /**
     * Validate image dimensions
     *
     * @param UploadedFile $file
     * @param int $maxWidth
     * @param int $maxHeight
     * @param int|null $minWidth
     * @param int|null $minHeight
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public function validateImageDimensions(
        UploadedFile $file,
        int $maxWidth,
        int $maxHeight,
        ?int $minWidth = null,
        ?int $minHeight = null
    ): array {
        if (!$this->isImage($file)) {
            return ['valid' => false, 'error' => 'File is not an image'];
        }

        try {
            $dimensions = getimagesize($file->getRealPath());

            if ($dimensions === false) {
                return ['valid' => false, 'error' => 'Could not read image dimensions'];
            }

            [$width, $height] = $dimensions;

            if ($width > $maxWidth || $height > $maxHeight) {
                return [
                    'valid' => false,
                    'error' => sprintf(
                        'Image dimensions (%dx%d) exceed maximum (%dx%d)',
                        $width,
                        $height,
                        $maxWidth,
                        $maxHeight
                    )
                ];
            }

            if ($minWidth !== null && $width < $minWidth) {
                return [
                    'valid' => false,
                    'error' => sprintf('Image width (%d) is less than minimum (%d)', $width, $minWidth)
                ];
            }

            if ($minHeight !== null && $height < $minHeight) {
                return [
                    'valid' => false,
                    'error' => sprintf('Image height (%d) is less than minimum (%d)', $height, $minHeight)
                ];
            }

            return ['valid' => true, 'error' => null];

        } catch (\Exception $e) {
            return ['valid' => false, 'error' => 'Error validating image: ' . $e->getMessage()];
        }
    }

    /**
     * Check if file is an image
     *
     * @param UploadedFile $file
     * @return bool
     */
    public function isImage(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Get allowed extensions list
     *
     * @return array
     */
    public function getAllowedExtensions(): array
    {
        return array_keys(self::ALLOWED_TYPES);
    }

    /**
     * Get allowed MIME types for an extension
     *
     * @param string $extension
     * @return array|null
     */
    public function getAllowedMimeTypes(string $extension): ?array
    {
        return self::ALLOWED_TYPES[$extension] ?? null;
    }
}
