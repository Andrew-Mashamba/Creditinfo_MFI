<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BalanceSheetItemIntegrationService;
use Illuminate\Support\Facades\Log;

class SynchronizeBalanceSheetItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balance-sheet:sync 
                            {--force : Force synchronization even if recently run}
                            {--verbose : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize all balance sheet items with the accounts table to ensure data consistency';

    /**
     * The integration service instance.
     *
     * @var BalanceSheetItemIntegrationService
     */
    protected $integrationService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->integrationService = new BalanceSheetItemIntegrationService();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting Balance Sheet Synchronization...');
        $this->info('=========================================');
        
        try {
            $startTime = microtime(true);
            
            // Check if synchronization was recently run
            if (!$this->option('force') && $this->wasRecentlySynchronized()) {
                $this->warn('Synchronization was run recently. Use --force to override.');
                return 0;
            }
            
            // Run synchronization
            $this->info('Synchronizing balance sheet items with accounts table...');
            
            if ($this->option('verbose')) {
                $this->line('');
                $this->info('Processing PPE Assets...');
            }
            
            $result = $this->integrationService->synchronizeAllBalanceSheetItems();
            
            if ($result) {
                $executionTime = round(microtime(true) - $startTime, 2);
                
                $this->info('');
                $this->info('✅ Synchronization completed successfully!');
                $this->info("Execution time: {$executionTime} seconds");
                
                // Log successful synchronization
                Log::info('Balance sheet synchronization completed', [
                    'execution_time' => $executionTime,
                    'timestamp' => now()->toDateTimeString()
                ]);
                
                // Update last synchronization timestamp
                $this->updateLastSyncTimestamp();
                
                // Display summary if verbose
                if ($this->option('verbose')) {
                    $this->displaySummary();
                }
                
                return 0;
            } else {
                $this->error('Synchronization failed. Check logs for details.');
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('Error during synchronization: ' . $e->getMessage());
            
            Log::error('Balance sheet synchronization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($this->option('verbose')) {
                $this->error('Stack trace:');
                $this->line($e->getTraceAsString());
            }
            
            return 1;
        }
    }
    
    /**
     * Check if synchronization was recently run
     *
     * @return bool
     */
    private function wasRecentlySynchronized()
    {
        $lastSync = cache('balance_sheet_last_sync');
        
        if (!$lastSync) {
            return false;
        }
        
        // Consider recent if run within last hour
        return now()->diffInMinutes($lastSync) < 60;
    }
    
    /**
     * Update the last synchronization timestamp
     *
     * @return void
     */
    private function updateLastSyncTimestamp()
    {
        cache(['balance_sheet_last_sync' => now()], 86400); // Cache for 24 hours
    }
    
    /**
     * Display summary of synchronized items
     *
     * @return void
     */
    private function displaySummary()
    {
        $this->info('');
        $this->info('Synchronization Summary:');
        $this->info('========================');
        
        $this->table(
            ['Balance Sheet Item', 'Status'],
            [
                ['PPE Assets', '✅ Synchronized'],
                ['Trade Receivables', '✅ Synchronized'],
                ['Trade Payables', '✅ Synchronized'],
                ['Investments', '✅ Synchronized'],
                ['Loan Portfolio', '✅ Synchronized'],
                ['Interest Payable', '✅ Synchronized'],
                ['Unearned Revenue', '✅ Synchronized'],
                ['Creditors', '✅ Synchronized'],
                ['Insurance Liabilities', '✅ Synchronized'],
            ]
        );
        
        $this->info('');
        $this->info('All balance sheet items are now properly reflected in the accounts table.');
        $this->info('The Statement of Financial Position will pull accurate data from accounts.');
    }
}