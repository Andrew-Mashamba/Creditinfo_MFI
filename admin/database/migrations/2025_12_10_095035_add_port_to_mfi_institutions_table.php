<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mfi_institutions', function (Blueprint $table) {
            $table->integer('port')->nullable()->after('database_name');
        });
    }

    public function down(): void
    {
        Schema::table('mfi_institutions', function (Blueprint $table) {
            $table->dropColumn('port');
        });
    }
};