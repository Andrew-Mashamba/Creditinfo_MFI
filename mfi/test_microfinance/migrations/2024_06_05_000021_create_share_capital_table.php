<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('SHARE_CAPITAL', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('category_code', 255)->nullable();
            $table->string('sub_category_code', 255)->nullable();
            $table->string('sub_category_name', 255)->nullable();
            $table->timestamp('updated_at', 0)->nullable();
            $table->timestamp('created_at', 0)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('SHARE_CAPITAL');
    }
}; 