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
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'status')) {
                $table->string('status', 20)->default('ACTIVE')->after('description');
                $table->index('status');
            }
            
            if (!Schema::hasColumn('roles', 'institution_id')) {
                $table->unsignedBigInteger('institution_id')->nullable()->after('status');
                $table->index('institution_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'status')) {
                $table->dropColumn('status');
            }
            
            if (Schema::hasColumn('roles', 'institution_id')) {
                $table->dropColumn('institution_id');
            }
        });
    }
};