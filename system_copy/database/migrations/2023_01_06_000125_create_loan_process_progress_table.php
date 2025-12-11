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
        Schema::create('loan_process_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id');
            $table->json('completed_tabs')->nullable();
            $table->json('tab_data')->nullable(); // Store additional tab-specific data
            $table->timestamps();
            
            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
            $table->unique('loan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_process_progress');
    }
};
