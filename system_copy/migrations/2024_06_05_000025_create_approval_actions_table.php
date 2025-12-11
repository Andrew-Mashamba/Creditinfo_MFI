<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_actions', function (Blueprint $table) {
            $table->integer('approver_id')->nullable();
            $table->text('status')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->integer('loan_id')->nullable();
            $table->integer('id')->primary();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_actions');
    }
}; 