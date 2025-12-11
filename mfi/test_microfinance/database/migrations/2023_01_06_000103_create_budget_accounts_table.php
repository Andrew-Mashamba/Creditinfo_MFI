<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_accounts', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('account_id')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('year', 20)->nullable();
            $table->string('branch', 255)->nullable();
            $table->timestamp('created_at', 0)->nullable();
            $table->timestamp('updated_at', 0)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_accounts');
    }
}; 