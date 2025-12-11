<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixLoanExceptionTracking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:fix-exception-tracking {--dry-run : Show what would be fixed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix loan exception tracking for loans with PENDING-EXCEPTIONS status but missing exception data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }
        
        // Find loans with exception status but missing exception tracking
        $loansToFix = DB::table('loans')
            ->where(function($query) {
                $query->where('status', 'PENDING-EXCEPTIONS')
                      ->orWhere('status', 'PENDING-WITH-EXCEPTIONS');
            })
            ->where(function($query) {
                $query->whereNull('has_exceptions')
                      ->orWhere('has_exceptions', false)
                      ->orWhereNull('exception_tracking_id')
                      ->orWhere('exception_tracking_id', '');
            })
            ->get();
        
        $this->info("Found {$loansToFix->count()} loans that need exception tracking fixes");
        
        if ($loansToFix->isEmpty()) {
            $this->info('No loans need fixing!');
            return 0;
        }
        
        $bar = $this->output->createProgressBar($loansToFix->count());
        $bar->start();
        
        $fixedCount = 0;
        
        foreach ($loansToFix as $loan) {
            $updateData = [
                'has_exceptions' => true,
                'exception_tracking_id' => 'EXC_' . $loan->loan_id . '_' . time(),
                'status' => 'PENDING-WITH-EXCEPTIONS' // Ensure consistent status
            ];
            
            if (!$isDryRun) {
                DB::table('loans')
                    ->where('id', $loan->id)
                    ->update($updateData);
            }
            
            $this->line("\nLoan ID: {$loan->loan_id} - Status: {$loan->status} -> PENDING-WITH-EXCEPTIONS");
            $fixedCount++;
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        if ($isDryRun) {
            $this->info("Would have fixed {$fixedCount} loans");
        } else {
            $this->info("Successfully fixed {$fixedCount} loans");
        }
        
        return 0;
    }
}
