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
        Schema::create('rate_limit_violations', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->index();
            $table->string('limiter', 50)->index(); // auth, api, uploads, etc.
            $table->string('endpoint', 255)->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, etc.
            $table->text('user_agent')->nullable();
            $table->string('referer', 255)->nullable();
            $table->timestamp('created_at')->index();

            // Composite index for analysis queries
            $table->index(['ip_address', 'limiter', 'created_at']);
            $table->index(['limiter', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rate_limit_violations');
    }
};
