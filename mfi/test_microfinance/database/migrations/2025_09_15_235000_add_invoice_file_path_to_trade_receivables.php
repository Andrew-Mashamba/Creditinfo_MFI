<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceFilePathToTradeReceivables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trade_receivables', function (Blueprint $table) {
            if (!Schema::hasColumn('trade_receivables', 'invoice_file_path')) {
                $table->string('invoice_file_path')->nullable()
                    ->after('invoice_attachment')
                    ->comment('Path to the generated invoice PDF file');
            }
            
            if (!Schema::hasColumn('trade_receivables', 'invoice_generated_at')) {
                $table->timestamp('invoice_generated_at')->nullable()
                    ->after('invoice_file_path')
                    ->comment('When the invoice PDF was generated');
            }
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
            if (Schema::hasColumn('trade_receivables', 'invoice_file_path')) {
                $table->dropColumn('invoice_file_path');
            }
            
            if (Schema::hasColumn('trade_receivables', 'invoice_generated_at')) {
                $table->dropColumn('invoice_generated_at');
            }
        });
    }
}