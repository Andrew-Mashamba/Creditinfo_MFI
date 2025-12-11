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
        Schema::create('scheduled_task_logs', function (Blueprint $table) {
            $table->id();
            $table->string('command');
            $table->string('category')->nullable();
            $table->enum('status', ['success', 'failed', 'running'])->default('running');
            $table->enum('trigger_type', ['scheduled', 'manual'])->default('scheduled');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->text('output')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('triggered_by')->nullable();
            $table->timestamps();

            $table->index('command');
            $table->index('status');
            $table->index('started_at');
            $table->index(['command', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scheduled_task_logs');
    }
};
