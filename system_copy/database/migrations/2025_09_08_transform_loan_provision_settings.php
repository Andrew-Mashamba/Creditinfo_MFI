<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // First, rename the old table if it exists and has the old structure
        if (Schema::hasTable('loan_provision_settings') && Schema::hasColumn('loan_provision_settings', 'per')) {
            Schema::rename('loan_provision_settings', 'loan_provision_settings_old_backup');
        }
        
        // Create new loan_provision_settings table with IFRS 9 structure
        if (!Schema::hasTable('loan_provision_settings')) {
            Schema::create('loan_provision_settings', function (Blueprint $table) {
                $table->id();
                $table->integer('stage1_days')->default(30);
                $table->integer('stage2_days')->default(90);
                $table->integer('stage3_days')->default(180);
                $table->decimal('stage1_rate', 5, 2)->default(1.0);
                $table->decimal('stage2_rate', 5, 2)->default(10.0);
                $table->decimal('stage3_rate', 5, 2)->default(100.0);
                $table->decimal('performing_rate', 5, 2)->default(1.0);
                $table->decimal('watch_rate', 5, 2)->default(5.0);
                $table->decimal('substandard_rate', 5, 2)->default(25.0);
                $table->decimal('doubtful_rate', 5, 2)->default(50.0);
                $table->decimal('loss_rate', 5, 2)->default(100.0);
                $table->decimal('default_pd', 8, 4)->default(0.05);
                $table->decimal('default_lgd', 8, 4)->default(0.45);
                $table->decimal('optimistic_adjustment', 5, 2)->default(-20.0);
                $table->decimal('base_adjustment', 5, 2)->default(0.0);
                $table->decimal('pessimistic_adjustment', 5, 2)->default(30.0);
                $table->decimal('sicr_threshold', 5, 2)->default(2.0);
                $table->integer('npl_threshold')->default(90);
                $table->boolean('enable_forward_looking')->default(true);
                $table->boolean('enable_collateral_adjustment')->default(true);
                $table->boolean('enable_guarantor_adjustment')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
            
            // Insert default settings
            DB::table('loan_provision_settings')->insert([
                'id' => 1,
                'stage1_days' => 30,
                'stage2_days' => 90,
                'stage3_days' => 180,
                'stage1_rate' => 1.0,
                'stage2_rate' => 10.0,
                'stage3_rate' => 100.0,
                'performing_rate' => 1.0,
                'watch_rate' => 5.0,
                'substandard_rate' => 25.0,
                'doubtful_rate' => 50.0,
                'loss_rate' => 100.0,
                'default_pd' => 0.05,
                'default_lgd' => 0.45,
                'optimistic_adjustment' => -20.0,
                'base_adjustment' => 0.0,
                'pessimistic_adjustment' => 30.0,
                'sicr_threshold' => 2.0,
                'npl_threshold' => 90,
                'enable_forward_looking' => true,
                'enable_collateral_adjustment' => true,
                'enable_guarantor_adjustment' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Drop the new table
        Schema::dropIfExists('loan_provision_settings');
        
        // Restore the old table if it was backed up
        if (Schema::hasTable('loan_provision_settings_old_backup')) {
            Schema::rename('loan_provision_settings_old_backup', 'loan_provision_settings');
        }
    }
};