<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scheduled_task_settings', function (Blueprint $table) {
            $table->id();
            $table->string('task_id')->unique(); // Unique identifier for the task
            $table->string('command'); // The artisan command
            $table->boolean('is_enabled')->default(true); // Enable/disable toggle
            $table->unsignedBigInteger('disabled_by')->nullable(); // User who disabled
            $table->timestamp('disabled_at')->nullable(); // When it was disabled
            $table->text('disabled_reason')->nullable(); // Optional reason for disabling
            $table->timestamps();

            $table->index('task_id');
            $table->index('command');
            $table->index('is_enabled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scheduled_task_settings');
    }
};
