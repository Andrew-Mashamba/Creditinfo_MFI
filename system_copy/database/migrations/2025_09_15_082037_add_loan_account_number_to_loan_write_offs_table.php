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
        Schema::table('loan_write_offs', function (Blueprint $table) {
            $table->string('loan_account_number')->nullable()->after('loan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_write_offs', function (Blueprint $table) {
            $table->dropColumn('loan_account_number');
        });
    }
};