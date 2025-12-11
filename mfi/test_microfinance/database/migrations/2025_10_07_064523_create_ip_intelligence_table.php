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
        Schema::create('ip_intelligence', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique()->index(); // IPv4 or IPv6

            // Geolocation
            $table->string('country_code', 2)->nullable()->index();
            $table->string('country_name', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('timezone', 50)->nullable();

            // ISP & Network
            $table->string('isp', 255)->nullable();
            $table->string('organization', 255)->nullable();
            $table->string('asn', 20)->nullable(); // Autonomous System Number
            $table->string('as_name', 255)->nullable();

            // IP Type Detection
            $table->boolean('is_vpn')->default(false)->index();
            $table->boolean('is_proxy')->default(false)->index();
            $table->boolean('is_tor')->default(false)->index();
            $table->boolean('is_relay')->default(false);
            $table->boolean('is_hosting')->default(false)->index(); // Datacenter/cloud
            $table->boolean('is_mobile')->default(false);
            $table->boolean('is_crawler')->default(false);
            $table->string('connection_type', 50)->nullable(); // residential, business, hosting, cellular

            // Risk Assessment
            $table->integer('risk_score')->default(0); // 0-100
            $table->enum('threat_level', ['none', 'low', 'medium', 'high', 'critical'])->default('none')->index();
            $table->json('threat_types')->nullable(); // ['spam', 'malware', 'botnet', etc]

            // Reputation
            $table->integer('reputation_score')->default(50); // 0-100
            $table->boolean('is_known_attacker')->default(false);
            $table->boolean('is_known_abuser')->default(false);
            $table->boolean('is_bogon')->default(false); // Reserved/unallocated IP

            // Usage Statistics
            $table->integer('total_requests')->default(0);
            $table->integer('successful_requests')->default(0);
            $table->integer('failed_requests')->default(0);
            $table->integer('fraud_attempts')->default(0);
            $table->integer('unique_accounts')->default(0);
            $table->integer('unique_devices')->default(0);

            // Behavioral Patterns
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_fraud_attempt_at')->nullable();
            $table->integer('requests_last_hour')->default(0);
            $table->integer('requests_last_day')->default(0);

            // Blocking
            $table->boolean('is_blocked')->default(false)->index();
            $table->timestamp('blocked_at')->nullable();
            $table->text('blocked_reason')->nullable();
            $table->timestamp('block_expires_at')->nullable();

            // Metadata
            $table->json('abuse_confidence')->nullable(); // Abuse confidence scores from various sources
            $table->json('metadata')->nullable();
            $table->timestamp('last_updated_at')->nullable(); // When IP data was last refreshed

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['country_code', 'risk_score']);
            $table->index(['is_vpn', 'is_proxy', 'is_tor']);
            $table->index(['reputation_score', 'fraud_attempts']);
            $table->index(['last_seen_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_intelligence');
    }
};
