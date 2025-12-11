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
        Schema::table('loans', function (Blueprint $table) {
            // Add exception tracking fields
            $table->boolean('has_exceptions')->default(false)->after('status');
            $table->string('exception_tracking_id', 50)->nullable()->after('has_exceptions');
            $table->timestamp('exceptions_cleared_at')->nullable()->after('exception_tracking_id');
            $table->unsignedBigInteger('exceptions_cleared_by')->nullable()->after('exceptions_cleared_at');
            
            // Add indexes for performance
            $table->index('has_exceptions');
            $table->index('exception_tracking_id');
            
            // Add foreign key for exceptions_cleared_by
            $table->foreign('exceptions_cleared_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['exceptions_cleared_by']);
            $table->dropIndex(['has_exceptions']);
            $table->dropIndex(['exception_tracking_id']);
            $table->dropColumn(['has_exceptions', 'exception_tracking_id', 'exceptions_cleared_at', 'exceptions_cleared_by']);
        });
    }
};
