<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loan_collateral', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id');
            $table->string('collateral_type', 100);
            $table->text('description')->nullable();
            $table->decimal('value', 15, 2);
            $table->string('location', 255)->nullable();
            $table->string('document_number', 100)->nullable();
            $table->string('document_type', 100)->nullable();
            $table->string('insurance_policy', 100)->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->string('verification_status', 50)->default('PENDING');
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['loan_id', 'collateral_type']);
            $table->index(['verification_status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('loan_collateral');
    }
}; 