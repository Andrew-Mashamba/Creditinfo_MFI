<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('GL_accounts', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('account_code', 255)->nullable();
            $table->string('account_name', 255)->nullable();
            $table->timestamp('created_at', 0)->nullable();
            $table->timestamp('updated_at', 0)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('GL_accounts');
    }
}; 