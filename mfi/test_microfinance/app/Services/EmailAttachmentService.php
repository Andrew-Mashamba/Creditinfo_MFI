<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class EmailAttachmentService
{
    protected $maxFileSize = 10485760; // 10MB in bytes
    protected $allowedMimeTypes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain', 'text/csv', 'application/zip', 'application/x-rar-compressed'
    ];
    
    public function uploadAttachment($file, $userId)
    {
        try {
            // Validate file
            $this->validateFile($file);
            
            // Generate unique filename
            $filename = $this->generateUniqueFilename($file);
            
            // Store file
            $path = $file->storeAs("email-attachments/{$userId}", $filename, 'local');
            
            // Create attachment record
            $attachmentData = [
                'user_id' => $userId,
                'original_name' => $file->getClientOriginalName(),
                'filename' => $filename,
                'path' => $path,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'created_at' => now()
            ];
            
            Log::info('[EMAIL_ATTACHMENT_SERVICE] File uploaded successfully', [
                'user_id' => $userId,
                'filename' => $filename,
                'size' => $file->getSize()
            ]);
            
            return $attachmentData;
            
        } catch (Exception $e) {
            Log::error('[EMAIL_ATTACHMENT_SERVICE] File upload failed', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    public function validateFile($file)
    {
        if (!$file->isValid()) {
            throw new Exception('Invalid file upload');
        }
        
        if ($file->getSize() > $this->maxFileSize) {
            throw new Exception('File size exceeds maximum limit of ' . ($this->maxFileSize / 1024 / 1024) . 'MB');
        }
        
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new Exception('File type not allowed');
        }
        
        return true;
    }
    
    protected function generateUniqueFilename($file)
    {
        $extension = $file->getClientOriginalExtension();
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $basename = Str::slug($basename);
        
        return $basename . '_' . time() . '_' . Str::random(8) . '.' . $extension;
    }
    
    public function getAttachmentUrl($path)
    {
        return Storage::url($path);
    }
    
    public function deleteAttachment($path)
    {
        try {
            if (Storage::exists($path)) {
                Storage::delete($path);
                Log::info('[EMAIL_ATTACHMENT_SERVICE] Attachment deleted', ['path' => $path]);
                return true;
            }
            return false;
        } catch (Exception $e) {
            Log::error('[EMAIL_ATTACHMENT_SERVICE] Failed to delete attachment', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    public function cleanupOrphanedAttachments($days = 7)
    {
        try {
            $cutoffDate = now()->subDays($days);
            
            // Find orphaned attachments (not linked to any email)
            $orphanedAttachments = \DB::table('email_attachments')
                ->leftJoin('emails', 'email_attachments.email_id', '=', 'emails.id')
                ->whereNull('emails.id')
                ->where('email_attachments.created_at', '<', $cutoffDate)
                ->get();
            
            foreach ($orphanedAttachments as $attachment) {
                $this->deleteAttachment($attachment->path);
                
                \DB::table('email_attachments')
                    ->where('id', $attachment->id)
                    ->delete();
            }
            
            Log::info('[EMAIL_ATTACHMENT_SERVICE] Cleaned up orphaned attachments', [
                'count' => $orphanedAttachments->count()
            ]);
            
        } catch (Exception $e) {
            Log::error('[EMAIL_ATTACHMENT_SERVICE] Failed to cleanup orphaned attachments', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    public function getAttachmentInfo($path)
    {
        try {
            if (!Storage::exists($path)) {
                return null;
            }
            
            return [
                'size' => Storage::size($path),
                'last_modified' => Storage::lastModified($path),
                'mime_type' => Storage::mimeType($path)
            ];
        } catch (Exception $e) {
            Log::error('[EMAIL_ATTACHMENT_SERVICE] Failed to get attachment info', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    public function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    public function getAllowedMimeTypes()
    {
        return $this->allowedMimeTypes;
    }
    
    public function getMaxFileSize()
    {
        return $this->maxFileSize;
    }
    
    public function getMaxFileSizeFormatted()
    {
        return $this->formatFileSize($this->maxFileSize);
    }
}