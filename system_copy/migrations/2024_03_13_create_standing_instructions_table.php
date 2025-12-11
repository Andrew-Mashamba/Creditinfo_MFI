<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('standing_instructions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->string('source_account_number', 50)->nullable();
            $table->unsignedBigInteger('source_bank_id')->nullable();
            $table->string('destination_account_name')->nullable();
            $table->string('bank', 50)->default('NBC')->nullable();
            $table->unsignedBigInteger('destination_account_id')->nullable();
            $table->unsignedBigInteger('saccos_branch_id')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('frequency', 20)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('reference_number', 50)->nullable();
            $table->string('service', 100)->nullable();
            $table->string('status', 20)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->text('description')->nullable();

            $table->foreign('member_id')->references('id')->on('clients');
            $table->foreign('source_bank_id')->references('id')->on('banks');
            $table->foreign('destination_account_id')->references('id')->on('accounts');
            $table->foreign('saccos_branch_id')->references('id')->on('branches');
        });
    }

    public function down()
    {
        Schema::dropIfExists('standing_instructions');
    }
}; 