<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            // Top-up loan related columns (only missing ones)
            $table->unsignedBigInteger('top_up_loan_id')->nullable()->after('approval_stage_role_name');
            $table->decimal('top_up_amount', 15, 2)->nullable()->after('top_up_loan_id');
            $table->string('top_up_loan_account')->nullable()->after('top_up_amount');
            $table->timestamp('top_up_processed_at')->nullable()->after('top_up_loan_account');
            $table->unsignedBigInteger('top_up_processed_by')->nullable()->after('top_up_processed_at');
            
            // Add foreign key constraint for top_up_loan_id
            $table->foreign('top_up_loan_id')->references('id')->on('loans')->onDelete('set null');
            
            // Add foreign key constraint for top_up_processed_by
            $table->foreign('top_up_processed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['top_up_loan_id']);
            $table->dropForeign(['top_up_processed_by']);
            
            // Drop columns
            $table->dropColumn([
                'top_up_loan_id',
                'top_up_amount',
                'top_up_loan_account',
                'top_up_processed_at',
                'top_up_processed_by'
            ]);
        });
    }
};
