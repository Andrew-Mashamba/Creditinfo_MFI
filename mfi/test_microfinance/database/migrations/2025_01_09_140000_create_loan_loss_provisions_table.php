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
        Schema::create('loan_loss_provisions', function (Blueprint $table) {
            $table->id();
            $table->date('provision_date')->index();
            $table->string('loan_id')->index();
            $table->string('client_number')->nullable();
            $table->string('loan_classification');
            $table->decimal('outstanding_balance', 15, 2);
            $table->decimal('provision_rate', 5, 2); // Percentage
            $table->decimal('provision_amount', 15, 2);
            $table->decimal('previous_provision', 15, 2)->default(0);
            $table->decimal('provision_adjustment', 15, 2); // Can be positive or negative
            $table->string('provision_type')->default('specific'); // 'general' or 'specific'
            $table->text('notes')->nullable();
            $table->integer('days_in_arrears')->default(0);
            $table->string('status')->default('active'); // 'active', 'released', 'written_off'
            $table->timestamps();
            
            $table->index(['provision_date', 'loan_classification']);
            $table->index(['loan_id', 'provision_date']);
        });

        // Create provision rates configuration table
        Schema::create('provision_rates_config', function (Blueprint $table) {
            $table->id();
            $table->string('classification')->unique();
            $table->decimal('min_days', 5, 0);
            $table->decimal('max_days', 5, 0)->nullable();
            $table->decimal('provision_rate', 5, 2);
            $table->string('provision_type')->default('specific');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default provision rates based on regulatory standards
        DB::table('provision_rates_config')->insert([
            [
                'classification' => 'PERFORMING',
                'min_days' => 0,
                'max_days' => 0,
                'provision_rate' => 1.00,
                'provision_type' => 'general',
                'description' => 'General provision for performing loans',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'classification' => 'WATCH',
                'min_days' => 1,
                'max_days' => 30,
                'provision_rate' => 5.00,
                'provision_type' => 'specific',
                'description' => 'Special mention loans with early signs of weakness',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'classification' => 'SUBSTANDARD',
                'min_days' => 31,
                'max_days' => 90,
                'provision_rate' => 25.00,
                'provision_type' => 'specific',
                'description' => 'Loans with defined weaknesses that may lead to loss',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'classification' => 'DOUBTFUL',
                'min_days' => 91,
                'max_days' => 180,
                'provision_rate' => 50.00,
                'provision_type' => 'specific',
                'description' => 'Full repayment is questionable based on existing conditions',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'classification' => 'LOSS',
                'min_days' => 181,
                'max_days' => null,
                'provision_rate' => 100.00,
                'provision_type' => 'specific',
                'description' => 'Loans considered uncollectible and should be written off',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // Create provision summary table for daily aggregates
        Schema::create('loan_loss_provision_summary', function (Blueprint $table) {
            $table->id();
            $table->date('summary_date')->unique();
            $table->integer('total_loans');
            $table->decimal('total_outstanding', 15, 2);
            $table->decimal('performing_balance', 15, 2)->default(0);
            $table->decimal('watch_balance', 15, 2)->default(0);
            $table->decimal('substandard_balance', 15, 2)->default(0);
            $table->decimal('doubtful_balance', 15, 2)->default(0);
            $table->decimal('loss_balance', 15, 2)->default(0);
            $table->decimal('general_provisions', 15, 2)->default(0);
            $table->decimal('specific_provisions', 15, 2)->default(0);
            $table->decimal('total_provisions', 15, 2);
            $table->decimal('provision_coverage_ratio', 5, 2); // Provisions / NPL %
            $table->decimal('npl_ratio', 5, 2); // Non-performing loans ratio
            $table->json('statistics')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_loss_provision_summary');
        Schema::dropIfExists('provision_rates_config');
        Schema::dropIfExists('loan_loss_provisions');
    }
};