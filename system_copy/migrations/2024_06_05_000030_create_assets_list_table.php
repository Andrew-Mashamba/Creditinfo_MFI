<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets_list', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name', 255);
            $table->string('type', 255);
            $table->decimal('value', 15, 2);
            $table->date('acquisition_date');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->string('source', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets_list');
    }
}; 