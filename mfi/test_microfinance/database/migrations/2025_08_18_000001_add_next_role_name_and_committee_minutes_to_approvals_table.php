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
        Schema::table('approvals', function (Blueprint $table) {
            $table->string('next_role_name')->nullable()->after('approver_id');
            $table->string('committee_minutes_path')->nullable()->after('next_role_name');
            $table->boolean('policy_adherence_confirmed')->nullable()->default(false)->after('committee_minutes_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approvals', function (Blueprint $table) {
            $table->dropColumn(['next_role_name', 'committee_minutes_path', 'policy_adherence_confirmed']);
        });
    }
};