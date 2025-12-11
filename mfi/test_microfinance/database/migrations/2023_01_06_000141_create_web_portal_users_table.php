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
        Schema::create('web_portal_users', function (Blueprint $table) {
            $table->id();
            
            // Client relationship
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('client_number')->index(); // For quick lookups
            
            // Authentication credentials
            $table->string('username')->unique(); // Can be member number, email, or phone
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password_hash');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token')->nullable();
            
            // Portal status and settings
            $table->boolean('is_active')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->string('locked_reason')->nullable();
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('last_failed_attempt')->nullable();
            
            // Login tracking
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->text('last_user_agent')->nullable();
            $table->integer('total_logins')->default(0);
            
            // Password management
            $table->string('password_reset_token')->nullable();
            $table->timestamp('password_reset_expires_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->boolean('force_password_change')->default(false);
            
            // Session management
            $table->string('current_session_id')->nullable();
            $table->timestamp('session_expires_at')->nullable();
            $table->json('active_sessions')->nullable(); // Track multiple sessions
            
            // Portal permissions and preferences
            $table->json('permissions')->nullable(); // Portal-specific permissions
            $table->json('preferences')->nullable(); // User preferences (theme, language, etc.)
            $table->string('preferred_language', 5)->default('en');
            $table->string('timezone')->default('Africa/Dar_es_Salaam');
            
            // Security settings
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
            $table->json('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            
            // Notification preferences
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->boolean('login_notifications')->default(true);
            $table->boolean('transaction_notifications')->default(true);
            
            // Portal access logs
            $table->timestamp('portal_registered_at');
            $table->foreignId('registered_by')->nullable()->constrained('users'); // SACCO employee who enabled access
            $table->timestamp('last_activity_at')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['client_id', 'is_active']);
            $table->index(['email', 'is_active']);
            $table->index(['phone', 'is_active']);
            $table->index(['client_number', 'is_active']);
            $table->index(['last_login_at']);
            $table->index(['is_locked', 'locked_at']);
            $table->index(['password_reset_token']);
            $table->index(['current_session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_portal_users');
    }
};
