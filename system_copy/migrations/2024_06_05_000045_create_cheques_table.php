<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cheques', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('customer_account', 40);
            $table->double('amount');
            $table->string('cheque_number', 40)->nullable();
            $table->bigInteger('branch');
            $table->string('finance_approver', 20)->nullable();
            $table->string('manager_approver', 20)->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_cleared')->default(false);
            $table->string('status', 40)->nullable();
            $table->string('bank_account', 40)->nullable();
            $table->timestamp('created_at', 0)->default('2024-06-05 15:01:34')->nullable(false);
            $table->timestamp('updated_at', 0)->default('2024-06-05 15:01:34')->nullable(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheques');
    }
}; 