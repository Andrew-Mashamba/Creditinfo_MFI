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
        Schema::create('device_fingerprints', function (Blueprint $table) {
            $table->id();
            $table->string('fingerprint_hash', 64)->unique()->index(); // SHA256 of combined fingerprint

            // Browser Fingerprinting Data
            $table->text('user_agent')->nullable();
            $table->string('browser_name', 50)->nullable();
            $table->string('browser_version', 50)->nullable();
            $table->string('os_name', 50)->nullable();
            $table->string('os_version', 50)->nullable();
            $table->string('device_type', 50)->nullable(); // desktop, mobile, tablet
            $table->string('device_vendor', 100)->nullable();

            // Screen & Display
            $table->string('screen_resolution', 50)->nullable(); // 1920x1080
            $table->integer('screen_color_depth')->nullable();
            $table->string('timezone', 50)->nullable();
            $table->string('language', 10)->nullable();
            $table->json('languages')->nullable(); // All accepted languages

            // Canvas & WebGL Fingerprinting
            $table->string('canvas_hash', 64)->nullable();
            $table->string('webgl_vendor', 255)->nullable();
            $table->string('webgl_renderer', 255)->nullable();
            $table->string('webgl_hash', 64)->nullable();

            // Audio Fingerprinting
            $table->string('audio_hash', 64)->nullable();

            // Fonts
            $table->json('fonts_available')->nullable();
            $table->string('fonts_hash', 64)->nullable();

            // Plugins & Features
            $table->json('plugins')->nullable();
            $table->boolean('cookies_enabled')->default(true);
            $table->boolean('local_storage_enabled')->default(true);
            $table->boolean('session_storage_enabled')->default(true);
            $table->boolean('indexed_db_enabled')->default(true);
            $table->boolean('do_not_track')->default(false);

            // Hardware
            $table->integer('cpu_cores')->nullable();
            $table->integer('memory_gb')->nullable();
            $table->boolean('touch_support')->default(false);
            $table->integer('max_touch_points')->nullable();

            // Platform Details
            $table->string('platform', 50)->nullable();
            $table->boolean('mobile')->default(false);

            // Device Reputation & Risk
            $table->integer('trust_score')->default(50); // 0-100
            $table->integer('risk_score')->default(50); // 0-100
            $table->enum('reputation', ['unknown', 'trusted', 'suspicious', 'malicious'])->default('unknown');

            // Usage Statistics
            $table->integer('total_sessions')->default(0);
            $table->integer('total_transactions')->default(0);
            $table->integer('successful_transactions')->default(0);
            $table->integer('failed_transactions')->default(0);
            $table->integer('fraud_attempts')->default(0);

            // Associated Entities
            $table->integer('unique_accounts_count')->default(0); // How many different accounts from this device
            $table->integer('unique_ips_count')->default(0); // How many different IPs

            // Tracking
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->foreignId('last_api_key_id')->nullable()->constrained('developer_api_keys')->onDelete('set null');

            // Flags
            $table->boolean('is_emulator')->default(false);
            $table->boolean('is_virtual_machine')->default(false);
            $table->boolean('is_headless')->default(false);
            $table->boolean('is_automation_tool')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('blocked_at')->nullable();
            $table->text('blocked_reason')->nullable();

            // Metadata
            $table->json('raw_fingerprint')->nullable(); // Complete raw data
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['trust_score', 'risk_score']);
            $table->index(['reputation', 'is_blocked']);
            $table->index(['last_seen_at']);
            $table->index(['unique_accounts_count', 'fraud_attempts']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_fingerprints');
    }
};
