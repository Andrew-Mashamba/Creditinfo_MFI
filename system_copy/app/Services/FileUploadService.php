<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\TemporaryUploadedFile;

class FileUploadService
{
    /**
     * Upload a single file
     *
     * @param TemporaryUploadedFile|UploadedFile|null $file
     * @param string $path
     * @param array $options
     * @return string|null
     */
    public function uploadFile($file, string $path, array $options = [])
    {
        if (!$file) {
            return null;
        }

        $filename = $this->generateFilename($file);
        $disk = $options['disk'] ?? 'public';
        $visibility = $options['visibility'] ?? 'public';

        return $file->storeAs($path, $filename, [
            'disk' => $disk,
            'visibility' => $visibility
        ]);
    }

    /**
     * Upload multiple files
     *
     * @param array $files
     * @param string $path
     * @param array $options
     * @return array
     */
    public function uploadMultipleFiles(array $files, string $path, array $options = [])
    {
        if (empty($files)) {
            return [];
        }

        $uploadedFiles = [];
        foreach ($files as $file) {
            if ($file instanceof TemporaryUploadedFile || $file instanceof UploadedFile) {
                $uploadedFiles[] = $this->uploadFile($file, $path, $options);
            }
        }

        return array_filter($uploadedFiles);
    }

    /**
     * Delete a file
     *
     * @param string|null $path
     * @param string $disk
     * @return bool
     */
    public function deleteFile(?string $path, string $disk = 'public'): bool
    {
        if (!$path) {
            return false;
        }

        return Storage::disk($disk)->delete($path);
    }

    /**
     * Delete multiple files
     *
     * @param array $paths
     * @param string $disk
     * @return void
     */
    public function deleteMultipleFiles(array $paths, string $disk = 'public'): void
    {
        foreach ($paths as $path) {
            $this->deleteFile($path, $disk);
        }
    }

    /**
     * Generate a unique filename
     *
     * @param TemporaryUploadedFile|UploadedFile $file
     * @return string
     */
    protected function generateFilename($file): string
    {
        $extension = $file->getClientOriginalExtension();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $sanitizedName = preg_replace('/[^a-zA-Z0-9]/', '_', $originalName);
        
        return sprintf(
            '%s_%s.%s',
            $sanitizedName,
            time(),
            $extension
        );
    }

    /**
     * Get file URL
     *
     * @param string|null $path
     * @param string $disk
     * @return string|null
     */
    public function getFileUrl(?string $path, string $disk = 'public'): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk($disk)->url($path);
    }

    /**
     * Check if file exists
     *
     * @param string|null $path
     * @param string $disk
     * @return bool
     */
    public function fileExists(?string $path, string $disk = 'public'): bool
    {
        if (!$path) {
            return false;
        }

        return Storage::disk($disk)->exists($path);
    }
} 