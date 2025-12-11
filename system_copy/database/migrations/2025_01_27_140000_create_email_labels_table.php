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
        Schema::create('email_labels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('color', 7)->default('#6B7280'); // Hex color
            $table->string('icon')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_system')->default(false); // System labels can't be deleted
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'name']);
            $table->index(['user_id', 'order_index']);
        });
        
        // Pivot table for email-label relationships
        Schema::create('email_label', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('email_id');
            $table->unsignedBigInteger('label_id');
            $table->timestamp('applied_at');
            
            $table->foreign('email_id')->references('id')->on('emails')->onDelete('cascade');
            $table->foreign('label_id')->references('id')->on('email_labels')->onDelete('cascade');
            $table->unique(['email_id', 'label_id']);
            $table->index('label_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_label');
        Schema::dropIfExists('email_labels');
    }
};