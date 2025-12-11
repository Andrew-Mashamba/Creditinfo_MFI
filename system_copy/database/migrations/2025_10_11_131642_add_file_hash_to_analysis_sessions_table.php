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
        Schema::table('analysis_sessions', function (Blueprint $table) {
            $table->string('file_hash', 64)->nullable()->after('file_path')->index();
            $table->decimal('opening_balance', 15, 2)->nullable()->after('closing_balance');
            $table->decimal('closing_balance', 15, 2)->nullable()->after('total_transactions');
            $table->json('metadata')->nullable()->after('reconciliation_summary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('analysis_sessions', function (Blueprint $table) {
            $table->dropColumn(['file_hash', 'opening_balance', 'closing_balance', 'metadata']);
        });
    }
};
