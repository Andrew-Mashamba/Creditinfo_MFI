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
        Schema::table('budget_managements', function (Blueprint $table) {
            // Add milestone tracking flags
            $table->boolean('milestone_50_recorded')->default(false)->after('utilization_percentage');
            $table->boolean('milestone_75_recorded')->default(false)->after('milestone_50_recorded');
            $table->boolean('milestone_100_recorded')->default(false)->after('milestone_75_recorded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_managements', function (Blueprint $table) {
            $table->dropColumn([
                'milestone_50_recorded',
                'milestone_75_recorded',
                'milestone_100_recorded'
            ]);
        });
    }
};