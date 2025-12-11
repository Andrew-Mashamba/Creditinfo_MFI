<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loan_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id');
            $table->string('stage_name', 100);
            $table->string('stage_type', 50);
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->string('approver_name', 255)->nullable();
            $table->string('status', 50)->default('PENDING');
            $table->text('comments')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->json('conditions')->nullable();
            $table->timestamps();

            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['loan_id', 'stage_name']);
            $table->index(['approver_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('loan_approvals');
    }
}; 