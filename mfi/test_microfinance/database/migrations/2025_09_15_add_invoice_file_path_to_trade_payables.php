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
        Schema::table('trade_payables', function (Blueprint $table) {
            // Add column for storing PDF file path
            if (!Schema::hasColumn('trade_payables', 'invoice_file_path')) {
                $table->string('invoice_file_path')->nullable()->after('notes');
            }
            
            // Add column for processing errors
            if (!Schema::hasColumn('trade_payables', 'processing_error')) {
                $table->text('processing_error')->nullable()->after('processing_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_payables', function (Blueprint $table) {
            $table->dropColumn(['invoice_file_path', 'processing_error']);
        });
    }
};