<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SetupExportsDirectory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exports:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up the exports directory with proper permissions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $exportsPath = storage_path('app/public/exports');
        
        if (!file_exists($exportsPath)) {
            if (mkdir($exportsPath, 0755, true)) {
                $this->info('Exports directory created successfully at: ' . $exportsPath);
            } else {
                $this->error('Failed to create exports directory');
                return 1;
            }
        } else {
            $this->info('Exports directory already exists at: ' . $exportsPath);
        }

        // Ensure proper permissions
        if (chmod($exportsPath, 0755)) {
            $this->info('Directory permissions set correctly');
        } else {
            $this->warn('Could not set directory permissions');
        }

        // Create .gitkeep to ensure directory is tracked
        $gitkeepFile = $exportsPath . '/.gitkeep';
        if (!file_exists($gitkeepFile)) {
            file_put_contents($gitkeepFile, '');
            $this->info('Created .gitkeep file');
        }

        $this->info('Exports directory setup completed successfully!');
        return 0;
    }
} 