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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique()->index();
            $table->string('client_name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('rate_limit')->default(1000); // Requests per hour
            $table->json('allowed_ips')->nullable(); // IP whitelist for this key
            $table->json('permissions')->nullable(); // Specific permissions
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for performance
            $table->index(['is_active', 'expires_at']);
            $table->index('last_used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
}; 