<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProcessingStatusToTradeReceivables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trade_receivables', function (Blueprint $table) {
            if (!Schema::hasColumn('trade_receivables', 'processing_status')) {
                $table->enum('processing_status', ['pending', 'processing', 'completed', 'failed'])
                    ->default('pending')
                    ->after('status')
                    ->comment('Status of invoice processing job');
            }
            
            if (!Schema::hasColumn('trade_receivables', 'processing_error')) {
                $table->text('processing_error')->nullable()
                    ->after('processing_status')
                    ->comment('Error message if processing failed');
            }
            
            // Add index for querying by processing status
            $table->index('processing_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trade_receivables', function (Blueprint $table) {
            $table->dropIndex(['processing_status']);
            
            if (Schema::hasColumn('trade_receivables', 'processing_status')) {
                $table->dropColumn('processing_status');
            }
            
            if (Schema::hasColumn('trade_receivables', 'processing_error')) {
                $table->dropColumn('processing_error');
            }
        });
    }
}