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
        Schema::table('depreciation_postings', function (Blueprint $table) {
            $table->bigInteger('reversed_by')->nullable()->after('status');
            $table->timestamp('reversed_at')->nullable()->after('reversed_by');
            $table->text('reversal_reason')->nullable()->after('reversed_at');
            $table->string('reversal_reference', 100)->nullable()->after('reversal_reason');
            
            // Add index for reversal tracking
            $table->index('reversed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('depreciation_postings', function (Blueprint $table) {
            $table->dropColumn(['reversed_by', 'reversed_at', 'reversal_reason', 'reversal_reference']);
        });
    }
};