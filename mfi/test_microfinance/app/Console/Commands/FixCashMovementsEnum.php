<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixCashMovementsEnum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:cash-movements-enum';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix the cash_movements table type enum constraint';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing cash_movements table enum constraint...');

        try {
            // Check if table exists and has data
            if (Schema::hasTable('cash_movements')) {
                $count = DB::table('cash_movements')->count();
                
                if ($count > 0) {
                    $this->error("Cannot fix: cash_movements table has {$count} records. Please backup data first.");
                    return 1;
                }

                $this->info('Dropping cash_movements table...');
                Schema::dropIfExists('cash_movements');
            }

            $this->info('Creating cash_movements table with correct schema...');
            
            // Create the table with the correct enum values (without foreign keys for now)
            Schema::create('cash_movements', function ($table) {
                $table->id();
                $table->string('reference', 100)->unique();
                $table->enum('type', ['till_to_vault', 'vault_to_till', 'till_to_till', 'external_deposit', 'external_withdrawal']);
                $table->unsignedBigInteger('from_till_id')->nullable();
                $table->unsignedBigInteger('to_till_id')->nullable();
                $table->unsignedBigInteger('strongroom_ledger_id')->nullable();
                $table->unsignedBigInteger('vault_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('initiated_by');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->unsignedBigInteger('approval_id')->nullable();
                $table->decimal('amount', 15, 2);
                $table->json('denomination_breakdown')->nullable();
                $table->text('reason')->nullable();
                $table->enum('status', ['pending', 'approved', 'completed', 'rejected', 'cancelled'])->default('pending');
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['from_till_id', 'to_till_id']);
                $table->index('strongroom_ledger_id');
                $table->index('vault_id');
                $table->index('status');
            });

            $this->info('Successfully fixed cash_movements table enum constraint!');
            $this->info('You can now use types: till_to_vault, vault_to_till, till_to_till, external_deposit, external_withdrawal');
            
            return 0;

        } catch (\Exception $e) {
            $this->error('Error fixing cash_movements table: ' . $e->getMessage());
            return 1;
        }
    }
}
