<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class SetupEmailSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:setup 
                            {--force : Force setup even if tables exist}
                            {--skip-migrations : Skip running migrations}
                            {--skip-seeder : Skip running seeder}
                            {--skip-config : Skip configuration setup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Complete setup for the email system including migrations, configuration, and sample data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('========================================');
        $this->info('   Email System Setup');
        $this->info('========================================');
        
        // Check if setup has already been run
        if ($this->isAlreadySetup() && !$this->option('force')) {
            $this->warn('Email system appears to be already set up.');
            if (!$this->confirm('Do you want to continue anyway?')) {
                return 0;
            }
        }
        
        // Step 1: Run migrations
        if (!$this->option('skip-migrations')) {
            $this->setupMigrations();
        }
        
        // Step 2: Configure environment
        if (!$this->option('skip-config')) {
            $this->setupConfiguration();
        }
        
        // Step 3: Create required directories
        $this->createDirectories();
        
        // Step 4: Set up scheduled tasks
        $this->setupScheduledTasks();
        
        // Step 5: Run seeder for sample data
        if (!$this->option('skip-seeder')) {
            $this->seedSampleData();
        }
        
        // Step 6: Create email log channel
        $this->setupLogging();
        
        // Step 7: Verify setup
        $this->verifySetup();
        
        $this->info('');
        $this->info('========================================');
        $this->info('   Email System Setup Complete!');
        $this->info('========================================');
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Configure your SMTP settings in .env file');
        $this->info('2. Run "php artisan queue:work" if using queued jobs');
        $this->info('3. Ensure cron is set up for scheduled tasks');
        $this->info('4. Access the email system through your application');
        
        return 0;
    }
    
    /**
     * Check if email system is already set up
     */
    protected function isAlreadySetup()
    {
        return Schema::hasTable('emails') && 
               Schema::hasTable('email_snoozes') && 
               Schema::hasTable('scheduled_emails');
    }
    
    /**
     * Run email system migrations
     */
    protected function setupMigrations()
    {
        $this->info('Running email system migrations...');
        
        $migrations = [
            '2025_01_27_create_emails_table.php',
            '2025_01_27_create_email_activity_logs_table.php',
            '2025_01_27_create_email_archives_table.php',
            '2025_01_27_add_message_id_to_emails_table.php',
            '2025_01_27_create_advanced_email_features_tables.php'
        ];
        
        foreach ($migrations as $migration) {
            $migrationPath = database_path("migrations/{$migration}");
            if (file_exists($migrationPath)) {
                $this->info("Running migration: {$migration}");
                Artisan::call('migrate', [
                    '--path' => "database/migrations/{$migration}",
                    '--force' => true
                ]);
            }
        }
        
        $this->info('✓ Migrations completed');
    }
    
    /**
     * Set up configuration
     */
    protected function setupConfiguration()
    {
        $this->info('Setting up email configuration...');
        
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);
        
        $emailConfig = [
            'EMAIL_SERVER' => 'zima',
            'EMAIL_SYNC_ENABLED' => 'true',
            'EMAIL_SYNC_INTERVAL' => '5',
            'EMAIL_SYNC_BATCH_SIZE' => '50',
            'EMAIL_SYNC_DAYS' => '30',
        ];
        
        $added = false;
        foreach ($emailConfig as $key => $value) {
            if (strpos($envContent, $key . '=') === false) {
                $envContent .= "\n{$key}={$value}";
                $added = true;
            }
        }
        
        if ($added) {
            file_put_contents($envPath, $envContent);
            $this->info('✓ Email configuration added to .env file');
        } else {
            $this->info('✓ Email configuration already exists');
        }
        
        // Create email-servers config if not exists
        $configPath = config_path('email-servers.php');
        if (!file_exists($configPath)) {
            $this->call('vendor:publish', [
                '--tag' => 'email-config',
                '--force' => true
            ]);
        }
    }
    
    /**
     * Create required directories
     */
    protected function createDirectories()
    {
        $this->info('Creating required directories...');
        
        $directories = [
            storage_path('app/email-attachments'),
            storage_path('app/email-backups'),
            storage_path('logs/email'),
        ];
        
        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
                $this->info("Created directory: {$directory}");
            }
        }
        
        $this->info('✓ Directories created');
    }
    
    /**
     * Set up scheduled tasks
     */
    protected function setupScheduledTasks()
    {
        $this->info('Setting up scheduled tasks...');
        
        $this->info('');
        $this->info('Add the following to your crontab:');
        $this->info('* * * * * cd ' . base_path() . ' && php artisan schedule:run >> /dev/null 2>&1');
        $this->info('');
        
        $this->info('Scheduled tasks configured:');
        $this->info('- Email sync: Every 5 minutes');
        $this->info('- Process snoozes: Every 5 minutes');
        $this->info('- Send scheduled emails: Every minute');
        $this->info('- Process undo queue: Every minute');
        $this->info('- Archive old emails: Daily at 2 AM');
        
        $this->info('✓ Scheduled tasks configured');
    }
    
    /**
     * Seed sample data
     */
    protected function seedSampleData()
    {
        $this->info('Seeding sample email data...');
        
        if (class_exists(\Database\Seeders\EmailsSeeder::class)) {
            Artisan::call('db:seed', [
                '--class' => 'EmailsSeeder',
                '--force' => true
            ]);
            $this->info('✓ Sample data seeded');
        } else {
            $this->warn('EmailsSeeder not found. Skipping sample data.');
        }
    }
    
    /**
     * Set up logging
     */
    protected function setupLogging()
    {
        $this->info('Setting up email logging...');
        
        $loggingConfig = config_path('logging.php');
        if (file_exists($loggingConfig)) {
            $config = require $loggingConfig;
            
            if (!isset($config['channels']['email'])) {
                $this->info('Email log channel needs to be added to config/logging.php');
                $this->info("Add this to the 'channels' array:");
                $this->info("
'email' => [
    'driver' => 'daily',
    'path' => storage_path('logs/email/email.log'),
    'level' => 'debug',
    'days' => 14,
],
");
            }
        }
        
        $this->info('✓ Logging configuration checked');
    }
    
    /**
     * Verify setup
     */
    protected function verifySetup()
    {
        $this->info('');
        $this->info('Verifying setup...');
        
        $checks = [
            'Emails table exists' => Schema::hasTable('emails'),
            'Email snoozes table exists' => Schema::hasTable('email_snoozes'),
            'Scheduled emails table exists' => Schema::hasTable('scheduled_emails'),
            'Email templates table exists' => Schema::hasTable('email_templates'),
            'Email tracking table exists' => Schema::hasTable('email_tracking'),
            'Smart compose table exists' => Schema::hasTable('smart_compose_history'),
        ];
        
        $allPassed = true;
        foreach ($checks as $check => $result) {
            if ($result) {
                $this->info("✓ {$check}");
            } else {
                $this->error("✗ {$check}");
                $allPassed = false;
            }
        }
        
        if ($allPassed) {
            $this->info('');
            $this->info('✓ All checks passed!');
        } else {
            $this->warn('');
            $this->warn('Some checks failed. Please review the setup.');
        }
        
        // Show statistics
        $this->info('');
        $this->info('Current statistics:');
        
        if (Schema::hasTable('emails')) {
            $emailCount = DB::table('emails')->count();
            $this->info("- Total emails: {$emailCount}");
        }
        
        if (Schema::hasTable('users')) {
            $userCount = DB::table('users')->count();
            $this->info("- Total users: {$userCount}");
        }
    }
}