<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('savings_types', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->text('summary')->nullable();
            $table->boolean('status')->default(true);
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_types');
    }
}; 