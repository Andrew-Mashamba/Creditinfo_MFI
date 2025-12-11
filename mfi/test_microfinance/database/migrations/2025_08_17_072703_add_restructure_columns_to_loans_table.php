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
            // Restructure loan related columns
            $table->unsignedBigInteger('restructure_loan_id')->nullable()->after('restructure_loanId');
            $table->decimal('restructure_amount', 15, 2)->nullable()->after('restructure_loan_id');
            $table->decimal('restructure_penalty_amount', 15, 2)->nullable()->after('restructure_amount');
            $table->string('restructure_loan_account')->nullable()->after('restructure_penalty_amount');
            $table->timestamp('restructure_processed_at')->nullable()->after('restructure_loan_account');
            $table->unsignedBigInteger('restructure_processed_by')->nullable()->after('restructure_processed_at');
            
            // Add foreign key constraint for restructure_loan_id
            $table->foreign('restructure_loan_id')->references('id')->on('loans')->onDelete('set null');
            
            // Add foreign key constraint for restructure_processed_by
            $table->foreign('restructure_processed_by')->references('id')->on('users')->onDelete('set null');
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
            $table->dropForeign(['restructure_loan_id']);
            $table->dropForeign(['restructure_processed_by']);
            
            // Drop columns
            $table->dropColumn([
                'restructure_loan_id',
                'restructure_amount',
                'restructure_penalty_amount',
                'restructure_loan_account',
                'restructure_processed_at',
                'restructure_processed_by'
            ]);
        });
    }
};
