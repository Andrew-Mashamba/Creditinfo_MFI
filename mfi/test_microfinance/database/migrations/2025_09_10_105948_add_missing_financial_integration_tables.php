<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create financial_periods table if it doesn't exist
        if (!Schema::hasTable('financial_periods')) {
            Schema::create('financial_periods', function (Blueprint $table) {
                $table->id();
                $table->integer('year');
                $table->integer('month')->nullable();
                $table->integer('quarter')->nullable();
                $table->enum('period_type', ['annual', 'quarterly', 'monthly']);
                $table->date('start_date');
                $table->date('end_date');
                $table->boolean('is_closed')->default(false);
                $table->timestamp('closed_at')->nullable();
                $table->unsignedBigInteger('closed_by')->nullable();
                $table->timestamps();
                
                $table->unique(['year', 'period_type', 'quarter', 'month']);
                $table->index(['year', 'period_type']);
                $table->index('is_closed');
            });
        }

        // Create financial_ratios table
        if (!Schema::hasTable('financial_ratios')) {
            Schema::create('financial_ratios', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('financial_period_id');
                $table->string('ratio_category'); // liquidity, solvency, profitability, efficiency
                $table->string('ratio_name');
                $table->decimal('value', 20, 4);
                $table->string('formula')->nullable();
                $table->decimal('benchmark_value', 20, 4)->nullable();
                $table->enum('trend', ['improving', 'stable', 'declining'])->nullable();
                $table->text('interpretation')->nullable();
                $table->timestamps();
                
                $table->foreign('financial_period_id')->references('id')->on('financial_periods')->onDelete('cascade');
                $table->index(['financial_period_id', 'ratio_category']);
            });
        }

        // Create financial_statement_notes table
        if (!Schema::hasTable('financial_statement_notes')) {
            Schema::create('financial_statement_notes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('financial_period_id');
                $table->string('note_category');
                $table->string('note_title');
                $table->text('note_content');
                $table->integer('display_order')->default(0);
                $table->string('related_statement')->nullable();
                $table->string('reference_number')->nullable();
                $table->boolean('is_mandatory')->default(false);
                $table->timestamps();
                
                $table->foreign('financial_period_id')->references('id')->on('financial_periods')->onDelete('cascade');
                $table->index(['financial_period_id', 'note_category']);
                $table->index('display_order');
            });
        }

        // Create financial_statement_snapshots table
        if (!Schema::hasTable('financial_statement_snapshots')) {
            Schema::create('financial_statement_snapshots', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('financial_period_id');
                $table->enum('statement_type', ['balance_sheet', 'income_statement', 'cash_flow', 'equity_changes', 'trial_balance']);
                $table->json('data');
                $table->string('version')->default('1.0');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->enum('status', ['draft', 'review', 'approved', 'published'])->default('draft');
                $table->timestamps();
                
                $table->foreign('financial_period_id')->references('id')->on('financial_periods')->onDelete('cascade');
                $table->index(['financial_period_id', 'statement_type', 'status']);
                $table->index('approved_at');
            });
        }

        // Create statement_relationships table
        if (!Schema::hasTable('statement_relationships')) {
            Schema::create('statement_relationships', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('financial_period_id');
                $table->string('source_statement');
                $table->string('source_item');
                $table->string('target_statement');
                $table->string('target_item');
                $table->string('relationship_type'); // flows_to, derives_from, equals, etc.
                $table->decimal('amount', 20, 2);
                $table->text('description')->nullable();
                $table->boolean('is_validated')->default(false);
                $table->timestamps();
                
                $table->foreign('financial_period_id')->references('id')->on('financial_periods')->onDelete('cascade');
                $table->index(['financial_period_id', 'source_statement', 'target_statement']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statement_relationships');
        Schema::dropIfExists('financial_statement_snapshots');
        Schema::dropIfExists('financial_statement_notes');
        Schema::dropIfExists('financial_ratios');
        Schema::dropIfExists('financial_periods');
    }
};