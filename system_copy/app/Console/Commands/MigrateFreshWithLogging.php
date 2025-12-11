<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateFreshWithLogging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:fresh-logged 
                            {--seed : Seed the database with records}
                            {--force : Force the operation to run when in production}
                            {--detailed : Show detailed output and stack traces}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrate:fresh with comprehensive error logging and reporting';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        
        $this->info('============================================');
        $this->info('MIGRATION WITH LOGGING STARTED');
        $this->info('============================================');
        $this->info('Timestamp: ' . now()->format('Y-m-d H:i:s'));
        
        Log::info('=== MIGRATION WITH LOGGING STARTED ===');
        
        try {
            // Step 1: Run migrate:fresh
            $this->info("\nStep 1: Running database migration...");
            Log::info('Step 1: Running database migration');
            
            $migrationStartTime = microtime(true);
            
            // Run migration
            $exitCode = Artisan::call('migrate:fresh', [
                '--force' => $this->option('force'),
            ], $this->output);
            
            if ($exitCode !== 0) {
                throw new \Exception("Migration failed with exit code: {$exitCode}");
            }
            
            $migrationTime = round(microtime(true) - $migrationStartTime, 2);
            $this->info("✅ Migration completed successfully in {$migrationTime} seconds");
            Log::info("Migration completed successfully in {$migrationTime} seconds");
            
            // Step 2: Run seeders if requested
            if ($this->option('seed')) {
                $this->info("\nStep 2: Running database seeders...");
                Log::info('Step 2: Running database seeders');
                
                // Check if we're in a transaction and commit it first
                if (DB::transactionLevel() > 0) {
                    DB::commit();
                    $this->warn('Committed pending transaction before seeding');
                    Log::warning('Committed pending transaction before seeding');
                }
                
                // Disable query log to prevent memory issues
                DB::disableQueryLog();
                
                // Run seeders with detailed output
                $seedingStartTime = microtime(true);
                
                try {
                    // Call the seeder directly to get our custom error handling
                    $seeder = new \Database\Seeders\DatabaseSeeder();
                    $seeder->run();
                    
                    $seedingTime = round(microtime(true) - $seedingStartTime, 2);
                    $this->info("✅ Seeding completed successfully in {$seedingTime} seconds");
                    Log::info("Seeding completed successfully in {$seedingTime} seconds");
                    
                } catch (\Exception $e) {
                    $seedingTime = round(microtime(true) - $seedingStartTime, 2);
                    $this->error("❌ Seeding failed after {$seedingTime} seconds");
                    Log::error("Seeding failed after {$seedingTime} seconds: " . $e->getMessage());
                    
                    // Show error details
                    $this->error('Error: ' . $e->getMessage());
                    
                    if ($this->option('detailed')) {
                        $this->error('Stack trace:');
                        $this->error($e->getTraceAsString());
                    }
                    
                    throw $e;
                }
            }
            
            // Step 3: Verify data integrity
            $this->info("\nStep 3: Verifying database integrity...");
            Log::info('Step 3: Verifying database integrity');
            
            $this->verifyDatabaseIntegrity();
            
            // Calculate total time
            $totalTime = round(microtime(true) - $startTime, 2);
            
            // Success summary
            $this->info("\n============================================");
            $this->info("✅ MIGRATION COMPLETED SUCCESSFULLY");
            $this->info("============================================");
            $this->info("Total execution time: {$totalTime} seconds");
            $this->info("Check storage/logs/laravel.log for detailed logs");
            
            Log::info("=== MIGRATION COMPLETED SUCCESSFULLY in {$totalTime} seconds ===");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            // Calculate total time
            $totalTime = round(microtime(true) - $startTime, 2);
            
            // Error summary
            $this->error("\n============================================");
            $this->error("❌ MIGRATION FAILED");
            $this->error("============================================");
            $this->error("Error: " . $e->getMessage());
            $this->error("Total execution time: {$totalTime} seconds");
            $this->error("Check storage/logs/laravel.log for detailed error information");
            
            Log::error("=== MIGRATION FAILED after {$totalTime} seconds ===");
            Log::error("Error: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            // Try to show current database state even on failure
            $this->info("\nAttempting to show current database state...");
            try {
                $this->verifyDatabaseIntegrity();
            } catch (\Exception $verifyException) {
                $this->error("Could not verify database state: " . $verifyException->getMessage());
            }
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Verify database integrity and show statistics
     */
    protected function verifyDatabaseIntegrity()
    {
        $criticalTables = [
            'branches' => 'Branches',
            'users' => 'Users',
            'institutions' => 'Institutions',
            'clients' => 'Clients',
            'employees' => 'Employees',
            'accounts' => 'Accounts',
            'loans' => 'Loans',
            'transactions' => 'Transactions',
        ];
        
        $this->info("\nDatabase Statistics:");
        $this->info(str_repeat('-', 40));
        
        $hasIssues = false;
        
        foreach ($criticalTables as $table => $label) {
            try {
                $count = DB::table($table)->count();
                $status = $count > 0 ? '✅' : '⚠️';
                $this->info("{$status} {$label}: {$count}");
                
                if ($count == 0 && in_array($table, ['branches', 'users'])) {
                    $hasIssues = true;
                    Log::warning("Critical table '{$table}' is empty!");
                }
                
            } catch (\Exception $e) {
                $this->error("❌ {$label}: Error - " . $e->getMessage());
                Log::error("Error checking table '{$table}': " . $e->getMessage());
            }
        }
        
        $this->info(str_repeat('-', 40));
        
        if ($hasIssues) {
            $this->warn("\n⚠️  WARNING: Some critical tables are empty!");
            $this->warn("This may indicate that seeders did not complete successfully.");
            Log::warning("Database integrity check found empty critical tables");
        } else {
            $this->info("\n✅ Database integrity check passed");
            Log::info("Database integrity check passed");
        }
        
        // Check foreign key constraints
        try {
            $this->checkForeignKeyIntegrity();
        } catch (\Exception $e) {
            $this->error("Foreign key integrity check failed: " . $e->getMessage());
            Log::error("Foreign key integrity check failed: " . $e->getMessage());
        }
    }
    
    /**
     * Check foreign key integrity
     */
    protected function checkForeignKeyIntegrity()
    {
        // Check if users have valid branch_id
        $usersWithInvalidBranch = DB::table('users')
            ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
            ->whereNotNull('users.branch_id')
            ->whereNull('branches.id')
            ->count();
            
        if ($usersWithInvalidBranch > 0) {
            $this->warn("⚠️  Found {$usersWithInvalidBranch} users with invalid branch_id");
            Log::warning("Found {$usersWithInvalidBranch} users with invalid branch_id");
        }
        
        // Check if clients have valid branch_id
        $clientsWithInvalidBranch = DB::table('clients')
            ->leftJoin('branches', 'clients.branch_id', '=', 'branches.id')
            ->whereNotNull('clients.branch_id')
            ->whereNull('branches.id')
            ->count();
            
        if ($clientsWithInvalidBranch > 0) {
            $this->warn("⚠️  Found {$clientsWithInvalidBranch} clients with invalid branch_id");
            Log::warning("Found {$clientsWithInvalidBranch} clients with invalid branch_id");
        }
    }
}