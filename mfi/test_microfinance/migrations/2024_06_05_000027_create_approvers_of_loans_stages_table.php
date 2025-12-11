<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvers_of_loans_stages', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('loan_id');
            $table->integer('stage_id');
            $table->integer('current_loans_stages_id');
            $table->string('stage_type', 255);
            $table->string('stage_name', 255);
            $table->integer('user_id')->nullable();
            $table->string('user_name', 255)->nullable();
            $table->string('status', 50);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvers_of_loans_stages');
    }
}; 