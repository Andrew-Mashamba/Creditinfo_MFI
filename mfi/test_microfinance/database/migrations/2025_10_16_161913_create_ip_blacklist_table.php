<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Only create if table doesn't exist
        if (!Schema::hasTable('ip_blacklist')) {
            Schema::create('ip_blacklist', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->index(); // Supports IPv4 and IPv6
            $table->text('reason')->nullable();
            $table->timestamp('expires_at')->nullable()->index(); // NULL = permanent block
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('auto_blocked')->default(false); // Auto-blocked by system vs manually added
            $table->integer('violation_count')->default(0); // Number of violations that triggered auto-block
            $table->string('blocked_by')->nullable(); // User ID or system
            $table->timestamps();

            // Index for active blocks query
            $table->index(['ip_address', 'is_active', 'expires_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ip_blacklist');
    }
};
