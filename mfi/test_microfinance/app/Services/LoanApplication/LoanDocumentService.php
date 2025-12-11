<?php

namespace App\Services\LoanApplication;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LoanDocumentService
{
    protected $allowedMimes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png'
    ];
    
    protected $maxFileSize = 10240; // 10MB in KB
    
    public function uploadDocument(UploadedFile $file, string $category, string $description, $loanId = null)
    {
        try {
            Log::info('LoanDocumentService: Starting document upload', [
                'filename' => $file->getClientOriginalName(),
                'category' => $category,
                'size' => $file->getSize(),
                'loan_id' => $loanId
            ]);
            
            // Validate file
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'error' => $validation['error']
                ];
            }
            
            // Generate unique filename
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = $this->generateUniqueFilename($originalName, $extension);
            
            // Determine storage path
            $storagePath = $this->getStoragePath($category);
            
            // Store the file
            $filePath = $file->storeAs($storagePath, $filename, 'public');
            
            if (!$filePath) {
                Log::error('LoanDocumentService: Failed to store file');
                return [
                    'success' => false,
                    'error' => 'Failed to store file'
                ];
            }
            
            $documentData = [
                'filename' => $filename,
                'original_name' => $originalName,
                'path' => $filePath,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'category' => $category,
                'description' => $description,
                'uploaded_at' => now()
            ];
            
            Log::info('LoanDocumentService: Document uploaded successfully', [
                'filename' => $filename,
                'path' => $filePath,
                'size' => $file->getSize()
            ]);
            
            return [
                'success' => true,
                'document' => $documentData
            ];
            
        } catch (\Exception $e) {
            Log::error('LoanDocumentService: Error uploading document', [
                'error' => $e->getMessage(),
                'filename' => $file->getClientOriginalName() ?? 'unknown'
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to upload document: ' . $e->getMessage()
            ];
        }
    }
    
    protected function validateFile(UploadedFile $file): array
    {
        // Check if file is valid
        if (!$file->isValid()) {
            return [
                'valid' => false,
                'error' => 'Invalid file upload'
            ];
        }
        
        // Check file size (convert to KB)
        $fileSizeKB = $file->getSize() / 1024;
        if ($fileSizeKB > $this->maxFileSize) {
            return [
                'valid' => false,
                'error' => 'File size exceeds maximum allowed size of ' . $this->maxFileSize . 'KB'
            ];
        }
        
        // Check MIME type
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!isset($this->allowedMimes[$extension]) || $this->allowedMimes[$extension] !== $mimeType) {
            return [
                'valid' => false,
                'error' => 'File type not allowed. Allowed types: ' . implode(', ', array_keys($this->allowedMimes))
            ];
        }
        
        return ['valid' => true];
    }
    
    protected function generateUniqueFilename(string $originalName, string $extension): string
    {
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $safeName = Str::slug($baseName, '_');
        $uniqueId = Str::random(8);
        $timestamp = now()->format('Ymd_His');
        
        return "{$safeName}_{$timestamp}_{$uniqueId}.{$extension}";
    }
    
    protected function getStoragePath(string $category): string
    {
        $basePath = 'loan_applications/documents';
        
        switch ($category) {
            case 'identity':
                return "{$basePath}/identity";
            case 'financial':
                return "{$basePath}/financial";
            case 'collateral':
                return "{$basePath}/collateral";
            case 'other':
                return "{$basePath}/other";
            default:
                return "{$basePath}/general";
        }
    }
    
    public function deleteDocument(string $filePath): bool
    {
        try {
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
                Log::info('LoanDocumentService: Document deleted successfully', ['path' => $filePath]);
                return true;
            }
            
            Log::warning('LoanDocumentService: File not found for deletion', ['path' => $filePath]);
            return false;
            
        } catch (\Exception $e) {
            Log::error('LoanDocumentService: Error deleting document', [
                'error' => $e->getMessage(),
                'path' => $filePath
            ]);
            return false;
        }
    }
    
    public function getDocumentUrl(string $filePath): string
    {
        return Storage::disk('public')->url($filePath);
    }
    
    public function getDocumentInfo(string $filePath): ?array
    {
        try {
            if (!Storage::disk('public')->exists($filePath)) {
                return null;
            }
            
            return [
                'path' => $filePath,
                'url' => $this->getDocumentUrl($filePath),
                'size' => Storage::disk('public')->size($filePath),
                'last_modified' => Storage::disk('public')->lastModified($filePath)
            ];
            
        } catch (\Exception $e) {
            Log::error('LoanDocumentService: Error getting document info', [
                'error' => $e->getMessage(),
                'path' => $filePath
            ]);
            return null;
        }
    }
}