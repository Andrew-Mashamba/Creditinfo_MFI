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
        Schema::table('ppes', function (Blueprint $table) {
            $table->timestamp('last_depreciation_date')->nullable()->after('closing_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppes', function (Blueprint $table) {
            $table->dropColumn('last_depreciation_date');
        });
    }
};