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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('complaint_categories')->onDelete('cascade');
            $table->foreignId('status_id')->constrained('complaint_statuses')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('priority')->default(1); // 1=Low, 2=Medium, 3=High, 4=Critical
            $table->string('reference_number')->unique()->nullable();
            $table->json('attachments')->nullable(); // Store file paths
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['client_id', 'status_id']);
            $table->index(['category_id', 'status_id']);
            $table->index(['status_id', 'created_at']);
            $table->index(['assigned_to', 'status_id']);
            $table->index('priority');
            $table->index('resolved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
