<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class ExportServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->ensureExportsDirectoryExists();
    }

    /**
     * Ensure the exports directory exists with proper permissions
     */
    private function ensureExportsDirectoryExists()
    {
        $exportsPath = storage_path('app/public/exports');
        
        if (!File::exists($exportsPath)) {
            File::makeDirectory($exportsPath, 0755, true);
        }
        
        // Ensure .gitkeep exists
        $gitkeepFile = $exportsPath . '/.gitkeep';
        if (!File::exists($gitkeepFile)) {
            File::put($gitkeepFile, '');
        }
    }
} 