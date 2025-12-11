<?php

namespace App\Traits;

use App\Services\FileUploadService;
use Livewire\TemporaryUploadedFile;

trait HasFileUploads
{
    protected FileUploadService $fileUploadService;

    /**
     * Initialize the file upload service
     */
    public function bootHasFileUploads(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Upload a single file
     *
     * @param TemporaryUploadedFile|null $file
     * @param string $path
     * @param array $options
     * @return string|null
     */
    protected function uploadFile($file, string $path, array $options = []): ?string
    {
        return $this->fileUploadService->uploadFile($file, $path, $options);
    }

    /**
     * Upload multiple files
     *
     * @param array $files
     * @param string $path
     * @param array $options
     * @return array
     */
    protected function uploadMultipleFiles(array $files, string $path, array $options = []): array
    {
        return $this->fileUploadService->uploadMultipleFiles($files, $path, $options);
    }

    /**
     * Delete a file
     *
     * @param string|null $path
     * @param string $disk
     * @return bool
     */
    protected function deleteFile(?string $path, string $disk = 'public'): bool
    {
        return $this->fileUploadService->deleteFile($path, $disk);
    }

    /**
     * Delete multiple files
     *
     * @param array $paths
     * @param string $disk
     * @return void
     */
    protected function deleteMultipleFiles(array $paths, string $disk = 'public'): void
    {
        $this->fileUploadService->deleteMultipleFiles($paths, $disk);
    }

    /**
     * Get file URL
     *
     * @param string|null $path
     * @param string $disk
     * @return string|null
     */
    protected function getFileUrl(?string $path, string $disk = 'public'): ?string
    {
        return $this->fileUploadService->getFileUrl($path, $disk);
    }

    /**
     * Check if file exists
     *
     * @param string|null $path
     * @param string $disk
     * @return bool
     */
    protected function fileExists(?string $path, string $disk = 'public'): bool
    {
        return $this->fileUploadService->fileExists($path, $disk);
    }

    /**
     * Handle file upload from Livewire component
     *
     * @param string $field
     * @param string $path
     * @param array $options
     * @return string|null
     */
    protected function handleFileUpload(string $field, string $path, array $options = []): ?string
    {
        if (!isset($this->$field) || !$this->$field) {
            return null;
        }

        $file = $this->$field;
        $uploadedPath = $this->uploadFile($file, $path, $options);
        
        if ($uploadedPath) {
            $this->$field = $uploadedPath;
        }

        return $uploadedPath;
    }

    /**
     * Handle multiple file uploads from Livewire component
     *
     * @param string $field
     * @param string $path
     * @param array $options
     * @return array
     */
    protected function handleMultipleFileUpload(string $field, string $path, array $options = []): array
    {
        if (!isset($this->$field) || !is_array($this->$field)) {
            return [];
        }

        $files = $this->$field;
        $uploadedPaths = $this->uploadMultipleFiles($files, $path, $options);
        
        if (!empty($uploadedPaths)) {
            $this->$field = $uploadedPaths;
        }

        return $uploadedPaths;
    }

    /**
     * Handle file removal from Livewire component
     *
     * @param string $field
     * @param int|null $index
     * @return void
     */
    protected function handleFileRemoval(string $field, ?int $index = null): void
    {
        if (!isset($this->$field)) {
            return;
        }

        if ($index !== null && is_array($this->$field)) {
            // Handle multiple file removal
            if (isset($this->$field[$index])) {
                $this->deleteFile($this->$field[$index]);
                unset($this->$field[$index]);
                $this->$field = array_values($this->$field);
            }
        } else {
            // Handle single file removal
            if (is_string($this->$field)) {
                $this->deleteFile($this->$field);
            }
            $this->$field = null;
        }
    }
} 