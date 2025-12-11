<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Group_loans', function (Blueprint $table) {
            $table->string('loan_id', 255)->nullable();
            $table->string('group_number', 255)->nullable();
            $table->double('amount')->nullable();
            $table->string('member_number', 255)->nullable();
            $table->string('status', 255)->nullable();
            $table->bigInteger('id')->primary();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Group_loans');
    }
}; 