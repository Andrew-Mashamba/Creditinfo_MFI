<?php

namespace App\Console\Commands;

use App\Services\TransactionPostingService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExecuteStandingInstructions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'standing-instructions:execute 
                            {--dry-run : Run without executing transactions}
                            {--instruction= : Execute specific instruction by ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute scheduled standing instructions';

    private TransactionPostingService $postingService;
    
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->postingService = new TransactionPostingService();
        $isDryRun = $this->option('dry-run');
        $specificId = $this->option('instruction');
        
        $this->info('Starting standing instructions execution...');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No transactions will be executed');
        }
        
        // Get instructions to execute
        $query = DB::table('standing_instructions')
            ->where('status', 'ACTIVE')
            ->whereDate('start_date', '<=', Carbon::today())
            ->where(function($q) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', Carbon::today());
            });
        
        if ($specificId) {
            $query->where('id', $specificId);
        } else {
            $query->whereDate('next_execution_date', '<=', Carbon::today());
        }
        
        $instructions = $query->get();
        
        if ($instructions->isEmpty()) {
            $this->info('No standing instructions to execute today.');
            return Command::SUCCESS;
        }
        
        $this->info("Found {$instructions->count()} instructions to process");
        
        $successCount = 0;
        $failureCount = 0;
        
        foreach ($instructions as $instruction) {
            try {
                $this->processInstruction($instruction, $isDryRun);
                $successCount++;
            } catch (\Exception $e) {
                $failureCount++;
                $this->error("Failed to process instruction {$instruction->id}: {$e->getMessage()}");
                Log::error("Standing instruction execution failed", [
                    'instruction_id' => $instruction->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        $this->info("Execution complete. Success: {$successCount}, Failed: {$failureCount}");
        
        return Command::SUCCESS;
    }
    
    private function processInstruction($instruction, $isDryRun = false)
    {
        $this->info("Processing instruction #{$instruction->id} - {$instruction->reference_number}");
        
        // Check if already executed today
        $alreadyExecuted = DB::table('standing_instructions_executions')
            ->where('standing_instruction_id', $instruction->id)
            ->whereDate('executed_at', Carbon::today())
            ->where('status', 'SUCCESS')
            ->exists();
        
        if ($alreadyExecuted) {
            $this->warn("Instruction #{$instruction->id} already executed today. Skipping.");
            return;
        }
        
        // Validate accounts exist and are active
        $sourceAccount = DB::table('accounts')
            ->where('id', $instruction->source_account_id)
            ->where('status', 'ACTIVE')
            ->first();
        
        $destAccount = DB::table('accounts')
            ->where('id', $instruction->destination_account_id)
            ->where('status', 'ACTIVE')
            ->first();
        
        if (!$sourceAccount) {
            throw new \Exception("Source account not found or inactive");
        }
        
        if (!$destAccount) {
            throw new \Exception("Destination account not found or inactive");
        }
        
        // Check sufficient balance
        if ($sourceAccount->balance < $instruction->amount) {
            $this->logFailure($instruction, 'INSUFFICIENT_BALANCE', 
                "Insufficient balance. Available: {$sourceAccount->balance}, Required: {$instruction->amount}");
            
            // Update next execution date even if failed
            $this->updateNextExecutionDate($instruction);
            return;
        }
        
        if ($isDryRun) {
            $this->info("DRY RUN: Would transfer {$instruction->amount} from {$instruction->source_account_number} to {$instruction->destination_account_number}");
            return;
        }
        
        // Execute the transaction
        $reference = $this->generateTransactionReference($instruction);
        
        try {
            DB::beginTransaction();
            
            // Use TransactionPostingService
            $result = $this->postingService->processTransaction(
                $instruction->source_account_number,
                $instruction->destination_account_number,
                $instruction->amount,
                $instruction->description . ' - Automated Standing Order',
                $reference,
                'STANDING_ORDER'
            );
            
            if (!isset($result['success']) || !$result['success']) {
                throw new \Exception($result['error'] ?? 'Transaction failed');
            }
            
            // Log successful execution
            DB::table('standing_instructions_executions')->insert([
                'standing_instruction_id' => $instruction->id,
                'executed_at' => now(),
                'amount' => $instruction->amount,
                'status' => 'SUCCESS',
                'transaction_reference' => $reference,
                'created_at' => now(),
            ]);
            
            // Update instruction statistics
            DB::table('standing_instructions')
                ->where('id', $instruction->id)
                ->update([
                    'last_execution_date' => Carbon::today(),
                    'execution_count' => DB::raw('execution_count + 1'),
                    'next_execution_date' => $this->calculateNextExecutionDate($instruction),
                    'updated_at' => now(),
                ]);
            
            // Check if instruction should be marked as completed
            if ($instruction->end_date && Carbon::parse($instruction->end_date)->isPast()) {
                DB::table('standing_instructions')
                    ->where('id', $instruction->id)
                    ->update(['status' => 'COMPLETED']);
                    
                $this->info("Instruction #{$instruction->id} completed (reached end date)");
            }
            
            DB::commit();
            
            $this->info("Successfully executed: {$instruction->amount} from {$instruction->source_account_number} to {$instruction->destination_account_number}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->logFailure($instruction, 'TRANSACTION_ERROR', $e->getMessage());
            
            // Still update next execution date
            $this->updateNextExecutionDate($instruction);
            
            throw $e;
        }
    }
    
    private function logFailure($instruction, $errorCode, $errorMessage)
    {
        // Log execution failure
        DB::table('standing_instructions_executions')->insert([
            'standing_instruction_id' => $instruction->id,
            'executed_at' => now(),
            'amount' => $instruction->amount,
            'status' => 'FAILED',
            'error_message' => $errorMessage,
            'created_at' => now(),
        ]);
        
        // Log detailed failure information
        DB::table('standing_instructions_failures')->insert([
            'standing_instruction_id' => $instruction->id,
            'failed_at' => now(),
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'error_context' => json_encode([
                'source_account' => $instruction->source_account_number,
                'destination_account' => $instruction->destination_account_number,
                'amount' => $instruction->amount,
            ]),
            'created_at' => now(),
        ]);
        
        $this->error("Failed: {$errorMessage}");
    }
    
    private function updateNextExecutionDate($instruction)
    {
        $nextDate = $this->calculateNextExecutionDate($instruction);
        
        DB::table('standing_instructions')
            ->where('id', $instruction->id)
            ->update([
                'next_execution_date' => $nextDate,
                'updated_at' => now(),
            ]);
    }
    
    private function calculateNextExecutionDate($instruction)
    {
        $currentNext = Carbon::parse($instruction->next_execution_date ?? $instruction->start_date);
        
        switch ($instruction->frequency) {
            case 'daily':
                return $currentNext->addDay()->format('Y-m-d');
                
            case 'weekly':
                return $currentNext->addWeek()->format('Y-m-d');
                
            case 'monthly':
                $nextMonth = $currentNext->copy()->addMonth();
                if ($instruction->day_of_month) {
                    $nextMonth->day = min($instruction->day_of_month, $nextMonth->daysInMonth);
                }
                return $nextMonth->format('Y-m-d');
                
            case 'quarterly':
                return $currentNext->addQuarter()->format('Y-m-d');
                
            case 'annually':
                return $currentNext->addYear()->format('Y-m-d');
                
            default:
                return $currentNext->addMonth()->format('Y-m-d');
        }
    }
    
    private function generateTransactionReference($instruction)
    {
        return 'SO-' . $instruction->reference_number . '-' . Carbon::now()->format('YmdHis');
    }
}