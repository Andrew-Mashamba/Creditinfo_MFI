<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Contributions', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('name', 100);
            $table->double('amount');
            $table->string('updated_at', 20);
            $table->string('created_at', 20);
            $table->string('status', 10);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Contributions');
    }
}; 