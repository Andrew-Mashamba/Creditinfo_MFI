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
            // Add accrued expense tracking fields
            $table->boolean('is_accrued')->default(false)->after('status');
            $table->timestamp('accrual_date')->nullable()->after('is_accrued');
            $table->date('expected_payment_date')->nullable()->after('accrual_date');
            $table->timestamp('realization_date')->nullable()->after('expected_payment_date');

            // Add indexes for better query performance
            $table->index('is_accrued');
            $table->index('accrual_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['is_accrued']);
            $table->dropIndex(['accrual_date']);

            // Drop columns
            $table->dropColumn([
                'is_accrued',
                'accrual_date',
                'expected_payment_date',
                'realization_date'
            ]);
        });
    }
};
