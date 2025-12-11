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
        Schema::table('clients', function (Blueprint $table) {
            // Add member_group_id column if it doesn't exist
            if (!Schema::hasColumn('clients', 'member_group_id')) {
                $table->unsignedBigInteger('member_group_id')->nullable()->after('branch_id');
                $table->foreign('member_group_id')->references('id')->on('member_groups')->onDelete('set null');
                $table->index('member_group_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'member_group_id')) {
                $table->dropForeign(['member_group_id']);
                $table->dropIndex(['member_group_id']);
                $table->dropColumn('member_group_id');
            }
        });
    }
};