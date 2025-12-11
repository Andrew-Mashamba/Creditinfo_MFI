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
        Schema::table('expenses', function (Blueprint $table) {
            // Add budget allocation tracking
            $table->foreignId('budget_allocation_id')->nullable()->after('budget_item_id')
                ->constrained('budget_allocations')->nullOnDelete();
            
            // Add index for faster queries
            $table->index('budget_allocation_id');
            $table->index(['budget_item_id', 'expense_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['budget_allocation_id']);
            $table->dropIndex(['budget_allocation_id']);
            $table->dropIndex(['budget_item_id', 'expense_month']);
            $table->dropColumn('budget_allocation_id');
        });
    }
};