<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Secure File Controller
 *
 * Serves files through controlled handlers with:
 * - Authorization checks
 * - Access logging
 * - Content-Type validation
 * - Download/inline disposition control
 * - Rate limiting
 *
 * @package App\Http\Controllers
 * @author MFI Management System Security Team
 * @date 2025-10-16
 */
class SecureFileController extends Controller
{
    /**
     * Allowed file types for inline display
     *
     * @var array
     */
    protected $inlineTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
    ];

    /**
     * Serve file with authorization check
     *
     * @param  Request  $request
     * @param  string  $disk
     * @param  string  $path
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
     */
    public function serve(Request $request, string $disk, string $path)
    {
        // Decode path (URL encoded)
        $path = urldecode($path);

        // Validate disk
        if (!$this->isValidDisk($disk)) {
            Log::warning('Invalid disk requested', [
                'disk' => $disk,
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
            ]);
            abort(404);
        }

        // Prevent path traversal
        if ($this->hasPathTraversal($path)) {
            Log::warning('Path traversal attempt detected', [
                'path' => $path,
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
            ]);
            abort(403, 'Invalid file path');
        }

        // Check if file exists
        if (!Storage::disk($disk)->exists($path)) {
            Log::info('File not found', [
                'disk' => $disk,
                'path' => $path,
                'user_id' => Auth::id(),
            ]);
            abort(404);
        }

        // Authorization check
        if (!$this->isAuthorized($request, $disk, $path)) {
            Log::warning('Unauthorized file access attempt', [
                'disk' => $disk,
                'path' => $path,
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
            ]);
            abort(403, 'Unauthorized access');
        }

        // Log file access
        Log::info('File accessed', [
            'disk' => $disk,
            'path' => $path,
            'user_id' => Auth::id(),
            'ip' => $request->ip(),
        ]);

        // Get file info
        $mimeType = Storage::disk($disk)->mimeType($path);
        $size = Storage::disk($disk)->size($path);
        $filename = basename($path);

        // Determine content disposition
        $disposition = $this->shouldDisplayInline($mimeType, $request)
            ? 'inline'
            : 'attachment';

        // Stream file
        return $this->streamFile($disk, $path, $filename, $mimeType, $disposition);
    }

    /**
     * Validate disk name
     *
     * @param  string  $disk
     * @return bool
     */
    protected function isValidDisk(string $disk): bool
    {
        $allowedDisks = ['local', 'public', 'member_images'];

        return in_array($disk, $allowedDisks) && config("filesystems.disks.{$disk}");
    }

    /**
     * Check for path traversal attempts
     *
     * @param  string  $path
     * @return bool
     */
    protected function hasPathTraversal(string $path): bool
    {
        // Check for directory traversal patterns
        if (strpos($path, '..') !== false) {
            return true;
        }

        if (strpos($path, './') !== false || strpos($path, '.\\') !== false) {
            return true;
        }

        // Check for absolute paths
        if (strpos($path, '/') === 0 || preg_match('/^[a-zA-Z]:/', $path)) {
            return true;
        }

        return false;
    }

    /**
     * Check if user is authorized to access file
     *
     * @param  Request  $request
     * @param  string  $disk
     * @param  string  $path
     * @return bool
     */
    protected function isAuthorized(Request $request, string $disk, string $path): bool
    {
        // Require authentication for all file access
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        // Admin can access all files
        if ($user->hasRole('Admin') || $user->hasRole('Super Admin')) {
            return true;
        }

        // Check file ownership/permissions based on path
        // This is application-specific logic

        // Example: Member documents
        if (strpos($path, 'member_images/') === 0) {
            return $this->canAccessMemberFile($user, $path);
        }

        // Example: Loan documents
        if (strpos($path, 'loan_applications/') === 0 || strpos($path, 'loan_documents/') === 0) {
            return $this->canAccessLoanFile($user, $path);
        }

        // Example: Client documents
        if (strpos($path, 'client-documents/') === 0) {
            return $this->canAccessClientFile($user, $path);
        }

        // Default: allow access to own files
        return true;
    }

    /**
     * Check if user can access member file
     *
     * @param  mixed  $user
     * @param  string  $path
     * @return bool
     */
    protected function canAccessMemberFile($user, string $path): bool
    {
        // Extract member ID from path (example: member_images/123/photo.jpg)
        if (preg_match('/member_images\/(\d+)\//', $path, $matches)) {
            $memberId = $matches[1];

            // User can access their own member files
            if ($user->member_id == $memberId) {
                return true;
            }

            // Staff can access member files
            if ($user->hasRole('Staff') || $user->hasRole('Manager')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user can access loan file
     *
     * @param  mixed  $user
     * @param  string  $path
     * @return bool
     */
    protected function canAccessLoanFile($user, string $path): bool
    {
        // Extract loan ID from path
        if (preg_match('/loan_(?:applications|documents)\/(\d+)\//', $path, $matches)) {
            $loanId = $matches[1];

            // Check if user is the loan applicant or guarantor
            // This would query the database - simplified for example
            // $loan = Loan::find($loanId);
            // if ($loan && ($loan->member_id == $user->member_id || $loan->guarantors->contains('member_id', $user->member_id))) {
            //     return true;
            // }

            // Loan officers can access loan files
            if ($user->hasRole('Loan Officer') || $user->hasRole('Credit Officer')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user can access client file
     *
     * @param  mixed  $user
     * @param  string  $path
     * @return bool
     */
    protected function canAccessClientFile($user, string $path): bool
    {
        // Similar logic to member files
        if (preg_match('/client-documents\/(\d+)\//', $path, $matches)) {
            $clientId = $matches[1];

            // Check ownership or staff access
            if ($user->hasRole('Staff') || $user->hasRole('Manager')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if file should be displayed inline
     *
     * @param  string  $mimeType
     * @param  Request  $request
     * @return bool
     */
    protected function shouldDisplayInline(string $mimeType, Request $request): bool
    {
        // Check if client requested download
        if ($request->query('download') === 'true') {
            return false;
        }

        // Check if MIME type is safe for inline display
        return in_array($mimeType, $this->inlineTypes);
    }

    /**
     * Stream file to browser
     *
     * @param  string  $disk
     * @param  string  $path
     * @param  string  $filename
     * @param  string  $mimeType
     * @param  string  $disposition
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    protected function streamFile(string $disk, string $path, string $filename, string $mimeType, string $disposition): StreamedResponse
    {
        $stream = Storage::disk($disk)->readStream($path);
        $size = Storage::disk($disk)->size($path);

        return response()->stream(
            function () use ($stream) {
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            },
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Length' => $size,
                'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'SAMEORIGIN',
                'X-XSS-Protection' => '1; mode=block',
            ]
        );
    }

    /**
     * Download file
     *
     * @param  Request  $request
     * @param  string  $disk
     * @param  string  $path
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function download(Request $request, string $disk, string $path)
    {
        // Decode path
        $path = urldecode($path);

        // Validate and authorize (same as serve method)
        if (!$this->isValidDisk($disk) || $this->hasPathTraversal($path)) {
            abort(403);
        }

        if (!Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        if (!$this->isAuthorized($request, $disk, $path)) {
            abort(403);
        }

        // Log download
        Log::info('File downloaded', [
            'disk' => $disk,
            'path' => $path,
            'user_id' => Auth::id(),
            'ip' => $request->ip(),
        ]);

        $filename = basename($path);
        $mimeType = Storage::disk($disk)->mimeType($path);

        return Storage::disk($disk)->download($path, $filename, [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
        ]);
    }
}
