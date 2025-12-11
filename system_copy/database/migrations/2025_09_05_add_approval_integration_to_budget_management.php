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
        Schema::table('budget_managements', function (Blueprint $table) {
            // Add approval tracking fields
            $table->bigInteger('approval_request_id')->nullable()->after('expense_account_id');
            $table->string('edit_approval_status', 50)->default('NONE')->after('approval_request_id');
            $table->json('pending_changes')->nullable()->after('edit_approval_status');
            $table->boolean('is_locked')->default(false)->after('pending_changes');
            $table->string('locked_reason')->nullable()->after('is_locked');
            $table->timestamp('locked_at')->nullable()->after('locked_reason');
            $table->bigInteger('locked_by')->nullable()->after('locked_at');
            
            // Add foreign keys
            $table->foreign('approval_request_id')->references('id')->on('approvals')->onDelete('set null');
            $table->foreign('locked_by')->references('id')->on('users')->onDelete('set null');
            
            // Add indexes for performance
            $table->index('approval_request_id');
            $table->index('edit_approval_status');
            $table->index('is_locked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_managements', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['approval_request_id']);
            $table->dropForeign(['locked_by']);
            
            // Drop indexes
            $table->dropIndex(['approval_request_id']);
            $table->dropIndex(['edit_approval_status']);
            $table->dropIndex(['is_locked']);
            
            // Drop columns
            $table->dropColumn([
                'approval_request_id',
                'edit_approval_status',
                'pending_changes',
                'is_locked',
                'locked_reason',
                'locked_at',
                'locked_by'
            ]);
        });
    }
};