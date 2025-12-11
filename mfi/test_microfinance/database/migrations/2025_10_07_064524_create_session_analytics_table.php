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
        Schema::create('session_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 64)->unique()->index();

            // Session Identity
            $table->foreignId('api_key_id')->nullable()->constrained('developer_api_keys')->onDelete('set null');
            $table->foreignId('device_fingerprint_id')->nullable()->constrained('device_fingerprints')->onDelete('set null');
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();

            // Session Timing
            $table->timestamp('session_start')->nullable();
            $table->timestamp('session_end')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->boolean('is_active')->default(true)->index();

            // Request Statistics
            $table->integer('total_requests')->default(0);
            $table->integer('successful_requests')->default(0);
            $table->integer('failed_requests')->default(0);
            $table->integer('fraud_flags')->default(0);
            $table->decimal('total_transaction_amount', 20, 2)->default(0);

            // Behavioral Metrics
            $table->integer('avg_request_interval_ms')->nullable(); // Average time between requests
            $table->integer('min_request_interval_ms')->nullable(); // Fastest request (bot detection)
            $table->integer('max_request_interval_ms')->nullable();
            $table->json('request_pattern')->nullable(); // Array of request timestamps
            $table->json('endpoint_sequence')->nullable(); // Order of endpoints accessed

            // Anomaly Indicators
            $table->integer('anomaly_score')->default(0); // 0-100
            $table->boolean('unusual_velocity')->default(false);
            $table->boolean('unusual_timing')->default(false); // e.g., all requests at exact intervals
            $table->boolean('unusual_pattern')->default(false);
            $table->boolean('bot_suspected')->default(false);
            $table->json('anomaly_indicators')->nullable();

            // Location & Context
            $table->string('country_code', 2)->nullable();
            $table->string('city', 100)->nullable();
            $table->boolean('location_mismatch')->default(false); // If location doesn't match account
            $table->boolean('timezone_mismatch')->default(false);

            // Device Behavior
            $table->integer('device_changes')->default(0); // How many times device fingerprint changed
            $table->integer('ip_changes')->default(0); // How many times IP changed
            $table->boolean('impossible_travel')->default(false); // Location changed too fast

            // Risk Assessment
            $table->integer('risk_score')->default(0); // 0-100
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low')->index();
            $table->text('risk_reason')->nullable();

            // Actions Taken
            $table->integer('transactions_completed')->default(0);
            $table->json('services_used')->nullable(); // Array of service types used
            $table->json('accounts_accessed')->nullable(); // Array of account numbers accessed

            // Metadata
            $table->json('metadata')->nullable();
            $table->json('raw_events')->nullable(); // Store individual events

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['session_start', 'session_end']);
            $table->index(['is_active', 'risk_level']);
            $table->index(['bot_suspected', 'fraud_flags']);
            $table->index(['anomaly_score', 'risk_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_analytics');
    }
};
