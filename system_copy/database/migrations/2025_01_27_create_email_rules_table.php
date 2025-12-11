<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Higher priority rules run first
            
            // Conditions (stored as JSON)
            $table->json('conditions'); // Array of condition objects
            $table->enum('condition_logic', ['all', 'any'])->default('all'); // AND/OR logic
            
            // Actions (stored as JSON)
            $table->json('actions'); // Array of action objects
            
            // Statistics
            $table->integer('times_applied')->default(0);
            $table->timestamp('last_applied_at')->nullable();
            
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'is_active', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_rules');
    }
};