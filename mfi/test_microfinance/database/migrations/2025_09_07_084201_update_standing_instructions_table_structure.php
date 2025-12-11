<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, backup existing data if any
        $existingData = DB::table('standing_instructions')->get();
        
        // Drop the old table
        Schema::dropIfExists('standing_instructions');
        
        // Create new table with updated structure
        Schema::create('standing_instructions', function (Blueprint $table) {
            $table->id();
            
            // Member and account information
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('source_account_id');
            $table->string('source_account_number');
            
            // Destination information
            $table->enum('destination_type', ['member', 'internal']);
            $table->unsignedBigInteger('destination_account_id');
            $table->string('destination_account_number');
            $table->string('destination_account_name')->nullable();
            
            // Transaction details
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->string('reference_number')->unique();
            
            // Schedule information
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'annually']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('day_of_month')->nullable();
            $table->integer('day_of_week')->nullable();
            $table->date('next_execution_date')->nullable();
            $table->date('last_execution_date')->nullable();
            $table->integer('execution_count')->default(0);
            
            // Status and metadata
            $table->enum('status', ['PENDING', 'ACTIVE', 'SUSPENDED', 'COMPLETED', 'FAILED', 'DELETED'])->default('PENDING');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('suspension_reason')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->unsignedBigInteger('suspended_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('member_id');
            $table->index('source_account_id');
            $table->index('destination_account_id');
            $table->index('status');
            $table->index('next_execution_date');
            $table->index('frequency');
            $table->index(['status', 'next_execution_date']);
        });
        
        // Create execution history table if it doesn't exist
        if (!Schema::hasTable('standing_instructions_executions')) {
            Schema::create('standing_instructions_executions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('standing_instruction_id');
                $table->timestamp('executed_at');
                $table->decimal('amount', 15, 2);
                $table->enum('status', ['SUCCESS', 'FAILED', 'PENDING']);
                $table->string('transaction_reference')->nullable();
                $table->text('error_message')->nullable();
                $table->integer('retry_count')->default(0);
                $table->timestamp('last_retry_at')->nullable();
                $table->timestamps();
                
                // Indexes
                $table->index('standing_instruction_id');
                $table->index('executed_at');
                $table->index('status');
                $table->index(['standing_instruction_id', 'executed_at']);
            });
        }
        
        // Create failures table if it doesn't exist
        if (!Schema::hasTable('standing_instructions_failures')) {
            Schema::create('standing_instructions_failures', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('standing_instruction_id');
                $table->unsignedBigInteger('execution_id')->nullable();
                $table->timestamp('failed_at');
                $table->string('error_code')->nullable();
                $table->text('error_message');
                $table->text('error_trace')->nullable();
                $table->json('error_context')->nullable();
                $table->boolean('resolved')->default(false);
                $table->timestamp('resolved_at')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->unsignedBigInteger('resolved_by')->nullable();
                $table->timestamps();
                
                // Indexes
                $table->index('standing_instruction_id');
                $table->index('execution_id');
                $table->index('failed_at');
                $table->index('resolved');
            });
        }
        
        // Migrate old data if any existed
        if ($existingData->count() > 0) {
            foreach ($existingData as $oldRecord) {
                try {
                    // Find source account ID
                    $sourceAccount = DB::table('accounts')
                        ->where('account_number', $oldRecord->source_account_number)
                        ->first();
                    
                    // Find destination account
                    $destAccount = DB::table('accounts')
                        ->where('id', $oldRecord->destination_account_id)
                        ->first();
                    
                    if ($sourceAccount && $destAccount) {
                        DB::table('standing_instructions')->insert([
                            'member_id' => $oldRecord->member_id,
                            'source_account_id' => $sourceAccount->id,
                            'source_account_number' => $oldRecord->source_account_number,
                            'destination_type' => $destAccount->member_number ? 'member' : 'internal',
                            'destination_account_id' => $oldRecord->destination_account_id,
                            'destination_account_number' => $destAccount->account_number,
                            'destination_account_name' => $oldRecord->destination_account_name,
                            'amount' => $oldRecord->amount,
                            'description' => $oldRecord->description ?? 'Standing Order',
                            'reference_number' => $oldRecord->reference_number,
                            'frequency' => strtolower($oldRecord->frequency),
                            'start_date' => $oldRecord->start_date,
                            'end_date' => $oldRecord->end_date,
                            'next_execution_date' => $oldRecord->start_date,
                            'status' => $oldRecord->status,
                            'created_at' => $oldRecord->created_at,
                            'updated_at' => $oldRecord->updated_at,
                        ]);
                    }
                } catch (\Exception $e) {
                    // Log migration error but continue
                    \Log::error('Failed to migrate standing instruction: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standing_instructions_failures');
        Schema::dropIfExists('standing_instructions_executions');
        Schema::dropIfExists('standing_instructions');
        
        // Recreate old structure
        Schema::create('standing_instructions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->string('source_account_number');
            $table->unsignedBigInteger('source_bank_id')->nullable();
            $table->string('destination_account_name')->nullable();
            $table->string('bank')->nullable();
            $table->unsignedBigInteger('destination_account_id');
            $table->unsignedBigInteger('saccos_branch_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('frequency');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('reference_number');
            $table->string('service')->nullable();
            $table->string('status')->default('PENDING');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();
        });
    }
};