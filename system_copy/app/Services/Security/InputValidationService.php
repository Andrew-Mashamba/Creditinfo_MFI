<?php

namespace App\Services\Security;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

/**
 * Input Validation Service
 *
 * Provides comprehensive input validation and sanitization to prevent injection attacks.
 * Use this service throughout the application for consistent security practices.
 *
 * @package App\Services\Security
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class InputValidationService
{
    /**
     * SQL-safe characters pattern (alphanumeric, space, common punctuation)
     */
    const SQL_SAFE_PATTERN = '/^[a-zA-Z0-9\s\-_.,@]+$/';

    /**
     * Filename-safe characters pattern
     */
    const FILENAME_SAFE_PATTERN = '/^[a-zA-Z0-9\-_.]+$/';

    /**
     * Validate and sanitize string input
     *
     * @param string|null $input
     * @param int $maxLength
     * @param bool $allowHtml
     * @return string
     */
    public function sanitizeString(?string $input, int $maxLength = 255, bool $allowHtml = false): string
    {
        if ($input === null) {
            return '';
        }

        // Trim whitespace
        $input = trim($input);

        // Limit length
        $input = Str::limit($input, $maxLength, '');

        // Remove HTML unless explicitly allowed
        if (!$allowHtml) {
            $input = strip_tags($input);
        }

        return $input;
    }

    /**
     * Sanitize input for safe use in SQL (use with parameter binding)
     * NOTE: This is NOT a replacement for parameter binding, but adds defense in depth
     *
     * @param string|null $input
     * @return string
     */
    public function sanitizeForSql(?string $input): string
    {
        if ($input === null) {
            return '';
        }

        $input = $this->sanitizeString($input, 1000, false);

        // Remove potentially dangerous SQL keywords/characters
        $dangerous = [
            'UNION', 'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'CREATE',
            'ALTER', 'EXEC', 'EXECUTE', 'SCRIPT', '--', '/*', '*/', 'xp_', 'sp_'
        ];

        foreach ($dangerous as $keyword) {
            $input = str_ireplace($keyword, '', $input);
        }

        return $input;
    }

    /**
     * Validate that a string contains only SQL-safe characters
     *
     * @param string $input
     * @return bool
     */
    public function isSqlSafe(string $input): bool
    {
        return preg_match(self::SQL_SAFE_PATTERN, $input) === 1;
    }

    /**
     * Sanitize filename to prevent path traversal
     *
     * @param string $filename
     * @return string
     */
    public function sanitizeFilename(string $filename): string
    {
        // Remove path components
        $filename = basename($filename);

        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9\-_.]/', '_', $filename);

        // Remove multiple dots (potential directory traversal)
        $filename = preg_replace('/\.{2,}/', '_', $filename);

        // Ensure it's not empty
        if (empty($filename) || $filename === '.') {
            $filename = 'unnamed_' . time();
        }

        return $filename;
    }

    /**
     * Generate a secure random filename
     *
     * @param string|null $originalFilename
     * @param string|null $extension
     * @return string
     */
    public function generateSecureFilename(?string $originalFilename = null, ?string $extension = null): string
    {
        $uuid = Str::uuid()->toString();

        if ($extension === null && $originalFilename !== null) {
            $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        }

        if ($extension) {
            // Sanitize extension
            $extension = strtolower(preg_replace('/[^a-z0-9]/', '', $extension));
            return $uuid . '.' . $extension;
        }

        return $uuid;
    }

    /**
     * Sanitize input for use in shell commands
     * WARNING: Always prefer not executing shell commands. Use this as last resort.
     *
     * @param string $input
     * @return string
     */
    public function sanitizeForShell(string $input): string
    {
        return escapeshellarg($input);
    }

    /**
     * Sanitize HTML output to prevent XSS
     *
     * @param string|null $html
     * @param array $allowedTags List of allowed HTML tags
     * @return string
     */
    public function sanitizeHtml(?string $html, array $allowedTags = []): string
    {
        if ($html === null) {
            return '';
        }

        if (empty($allowedTags)) {
            // Default safe tags for rich text
            $allowedTags = [
                'p', 'br', 'strong', 'em', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                'ul', 'ol', 'li', 'a', 'blockquote', 'code', 'pre'
            ];
        }

        $allowedString = '<' . implode('><', $allowedTags) . '>';
        return strip_tags($html, $allowedString);
    }

    /**
     * Escape output for safe display in Blade templates
     *
     * @param string|null $text
     * @param bool $preserveLineBreaks
     * @return string
     */
    public function escapeOutput(?string $text, bool $preserveLineBreaks = false): string
    {
        if ($text === null) {
            return '';
        }

        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if ($preserveLineBreaks) {
            $escaped = nl2br($escaped);
        }

        return $escaped;
    }

    /**
     * Validate email address
     *
     * @param string $email
     * @return bool
     */
    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate URL
     *
     * @param string $url
     * @param array $allowedSchemes
     * @return bool
     */
    public function isValidUrl(string $url, array $allowedSchemes = ['http', 'https']): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        return in_array($scheme, $allowedSchemes);
    }

    /**
     * Validate integer input
     *
     * @param mixed $input
     * @param int|null $min
     * @param int|null $max
     * @return int|null
     */
    public function validateInteger($input, ?int $min = null, ?int $max = null): ?int
    {
        if (!is_numeric($input)) {
            return null;
        }

        $value = (int) $input;

        if ($min !== null && $value < $min) {
            return null;
        }

        if ($max !== null && $value > $max) {
            return null;
        }

        return $value;
    }

    /**
     * Validate and sanitize array of IDs
     *
     * @param array $ids
     * @return array
     */
    public function sanitizeIds(array $ids): array
    {
        return array_filter(array_map(function ($id) {
            return $this->validateInteger($id, 1);
        }, $ids), function ($id) {
            return $id !== null;
        });
    }

    /**
     * Validate date input
     *
     * @param string $date
     * @param string $format
     * @return bool
     */
    public function isValidDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Sanitize JSON input
     *
     * @param string $json
     * @return array|null
     */
    public function sanitizeJson(string $json): ?array
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return is_array($data) ? $data : null;
        } catch (\JsonException $e) {
            return null;
        }
    }

    /**
     * Validate that input matches expected format
     *
     * @param string $input
     * @param string $pattern Regex pattern
     * @return bool
     */
    public function matchesPattern(string $input, string $pattern): bool
    {
        return preg_match($pattern, $input) === 1;
    }

    /**
     * Whitelist validation - check if value is in allowed list
     *
     * @param mixed $value
     * @param array $whitelist
     * @param mixed $default
     * @return mixed
     */
    public function whitelist($value, array $whitelist, $default = null)
    {
        return in_array($value, $whitelist, true) ? $value : $default;
    }

    /**
     * Validate phone number (basic)
     *
     * @param string $phone
     * @return bool
     */
    public function isValidPhone(string $phone): bool
    {
        // Remove common formatting characters
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);

        // Check if it's digits only and reasonable length
        return preg_match('/^\+?[0-9]{9,15}$/', $cleaned) === 1;
    }

    /**
     * Sanitize credit card number (for display)
     * Returns masked version: **** **** **** 1234
     *
     * @param string $cardNumber
     * @return string
     */
    public function maskCreditCard(string $cardNumber): string
    {
        $cleaned = preg_replace('/\D/', '', $cardNumber);

        if (strlen($cleaned) < 4) {
            return '****';
        }

        return str_repeat('*', strlen($cleaned) - 4) . substr($cleaned, -4);
    }

    /**
     * Validate and sanitize search input
     * Removes potentially dangerous characters for search queries
     *
     * @param string $search
     * @param int $maxLength
     * @return string
     */
    public function sanitizeSearch(string $search, int $maxLength = 100): string
    {
        $search = $this->sanitizeString($search, $maxLength, false);

        // Remove SQL wildcards and operators
        $search = str_replace(['%', '_', '*', '?'], '', $search);

        return $search;
    }

    /**
     * Comprehensive validation using Laravel's validator
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @return array ['valid' => bool, 'errors' => array, 'data' => array]
     */
    public function validate(array $data, array $rules, array $messages = []): array
    {
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->all(),
                'data' => []
            ];
        }

        return [
            'valid' => true,
            'errors' => [],
            'data' => $validator->validated()
        ];
    }
}
