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
        Schema::dropIfExists('smart_compose_history');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('smart_compose_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->text('current_text');
            $table->text('suggestions');
            $table->string('recipient_email')->nullable();
            $table->string('subject')->nullable();
            $table->json('accepted_suggestions')->nullable();
            $table->json('rejected_suggestions')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'created_at']);
        });
    }
};
