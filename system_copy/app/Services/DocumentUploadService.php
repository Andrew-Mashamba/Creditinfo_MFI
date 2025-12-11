<?php

namespace App\Services\LoanApplication;

use App\Models\Loan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service for handling loan document management
 */
class LoanDocumentService
{
    protected string $storagePath = 'loan-documents';
    
    /**
     * Upload and store a document
     */
    public function uploadDocument(UploadedFile $file, string $category, string $description): array
    {
        try {
            // Generate unique filename
            $filename = $this->generateFilename($file);
            
            // Store file
            $path = $file->storeAs(
                $this->storagePath . '/' . date('Y/m'),
                $filename,
                'local'
            );
            
            // Get file metadata
            $metadata = [
                'original_name' => $file->getClientOriginalName(),
                'filename' => $filename,
                'path' => $path,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'category' => $category,
                'description' => $description,
                'uploaded_at' => now(),
            ];
            
            Log::info('Document uploaded successfully', [
                'filename' => $filename,
                'category' => $category,
            ]);
            
            return [
                'success' => true,
                'document' => $metadata,
            ];
            
        } catch (\Exception $e) {
            Log::error('Document upload failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to upload document: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Validate document
     */
    public function validateDocument(UploadedFile $file, string $category): array
    {
        $errors = [];
        
        // Check file size
        $maxSize = $this->getMaxSizeForCategory($category);
        if ($file->getSize() > $maxSize) {
            $errors[] = 'File size exceeds maximum allowed (' . $this->formatBytes($maxSize) . ')';
        }
        
        // Check file type
        $allowedTypes = $this->getAllowedTypesForCategory($category);
        if (!in_array($file->getClientOriginalExtension(), $allowedTypes)) {
            $errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes);
        }
        
        // Scan for viruses if configured
        if (config('loan.scan_documents')) {
            $scanResult = $this->scanForViruses($file);
            if (!$scanResult['clean']) {
                $errors[] = 'File failed security scan';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
    
    /**
     * Attach documents to loan
     */
    public function attachDocuments(Loan $loan, array $documents): void
    {
        foreach ($documents as $document) {
            \DB::table('loan_images')->insert([
                'loan_id' => $loan->loan_id,
                'category' => $document['category'],
                'filename' => $document['original_name'],
                'url' => $document['path'],
                'file_size' => $document['size'],
                'mime_type' => $document['mime_type'],
                'description' => $document['description'],
                'uploaded_by' => \Auth::id(),
                'created_at' => $document['uploaded_at'],
            ]);
        }
    }
    
    /**
     * Get required documents for loan product
     */
    public function getRequiredDocuments(string $productId): array
    {
        // This would typically come from database configuration
        return [
            [
                'category' => 'identity',
                'name' => 'National ID',
                'description' => 'Copy of national identification card',
                'required' => true,
            ],
            [
                'category' => 'income',
                'name' => 'Salary Slip',
                'description' => 'Latest 3 months salary slips',
                'required' => true,
            ],
            [
                'category' => 'collateral',
                'name' => 'Collateral Documents',
                'description' => 'Ownership documents for physical collateral',
                'required' => false,
            ],
            [
                'category' => 'other',
                'name' => 'Additional Documents',
                'description' => 'Any other supporting documents',
                'required' => false,
            ],
        ];
    }
    
    /**
     * Generate unique filename
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $random = Str::random(8);
        
        return "{$timestamp}_{$random}.{$extension}";
    }
    
    /**
     * Get maximum file size for category
     */
    protected function getMaxSizeForCategory(string $category): int
    {
        $sizes = [
            'identity' => 5 * 1024 * 1024, // 5MB
            'income' => 10 * 1024 * 1024, // 10MB
            'collateral' => 20 * 1024 * 1024, // 20MB
            'other' => 10 * 1024 * 1024, // 10MB
        ];
        
        return $sizes[$category] ?? 10 * 1024 * 1024;
    }
    
    /**
     * Get allowed file types for category
     */
    protected function getAllowedTypesForCategory(string $category): array
    {
        $types = [
            'identity' => ['pdf', 'jpg', 'jpeg', 'png'],
            'income' => ['pdf', 'jpg', 'jpeg', 'png'],
            'collateral' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
            'other' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
        ];
        
        return $types[$category] ?? ['pdf', 'jpg', 'jpeg', 'png'];
    }
    
    /**
     * Scan file for viruses
     */
    protected function scanForViruses(UploadedFile $file): array
    {
        // Placeholder for virus scanning integration
        // In production, integrate with ClamAV or similar
        return ['clean' => true];
    }
    
    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Delete document
     */
    public function deleteDocument(string $path): bool
    {
        try {
            if (Storage::exists($path)) {
                Storage::delete($path);
                Log::info('Document deleted', ['path' => $path]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to delete document', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Get document download URL
     */
    public function getDocumentUrl(string $path): ?string
    {
        if (!Storage::exists($path)) {
            return null;
        }
        
        return Storage::temporaryUrl($path, now()->addMinutes(5));
    }
}