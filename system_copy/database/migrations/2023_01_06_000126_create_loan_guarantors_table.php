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
        Schema::create('loan_guarantors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
            $table->foreignId('guarantor_member_id')->constrained('clients')->onDelete('restrict');
            $table->enum('guarantor_type', ['self_guarantee', 'third_party_guarantee']);
            $table->string('relationship')->nullable(); // For third-party guarantors
            $table->decimal('total_guaranteed_amount', 15, 2)->default(0);
            $table->decimal('available_amount', 15, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'released'])->default('active');
            $table->timestamp('guarantee_start_date')->useCurrent();
            $table->timestamp('guarantee_end_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['loan_id', 'status']);
            $table->index(['guarantor_member_id', 'status']);
            $table->unique(['loan_id', 'guarantor_member_id']); // One guarantor per loan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_guarantors');
    }
};
