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
        Schema::create('provision_approvals', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 20, 2);
            $table->string('provision_method', 50);
            $table->string('source_account', 50);
            $table->string('reserve_account', 50);
            $table->text('reason')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'CANCELLED'])->default('PENDING');
            $table->unsignedBigInteger('requested_by');
            $table->timestamp('requested_at');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_comments')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('requested_by');
            $table->index('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provision_approvals');
    }
};