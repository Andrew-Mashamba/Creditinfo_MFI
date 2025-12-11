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
        // Create emails table if it doesn't exist
        if (!Schema::hasTable('emails')) {
            Schema::create('emails', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sender_id')->nullable();
                $table->unsignedBigInteger('recipient_id')->nullable();
                $table->string('sender_email')->nullable();
                $table->string('recipient_email')->nullable();
                $table->string('cc')->nullable();
                $table->string('bcc')->nullable();
                $table->string('subject');
                $table->text('body');
                $table->string('folder')->default('inbox');
                $table->boolean('is_read')->default(false);
                $table->boolean('is_sent')->default(false);
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->timestamps();
                
                $table->foreign('sender_id')->references('id')->on('users');
                $table->foreign('recipient_id')->references('id')->on('users');
                $table->index(['folder', 'is_read']);
                $table->index('sent_at');
            });
        }
        
        Schema::create('email_tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('email_id');
            $table->string('tracking_id')->unique();
            $table->boolean('track_opens')->default(true);
            $table->boolean('track_clicks')->default(true);
            $table->integer('open_count')->default(0);
            $table->timestamp('first_opened_at')->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->json('open_details')->nullable(); // IP, user agent, location
            $table->json('link_clicks')->nullable(); // Array of clicked links with timestamps
            $table->timestamps();
            
            $table->foreign('email_id')->references('id')->on('emails')->onDelete('cascade');
            $table->index('tracking_id');
        });
        
        // Add tracking columns to emails table
        Schema::table('emails', function (Blueprint $table) {
            $table->boolean('tracking_enabled')->default(false)->after('is_flagged');
            $table->string('tracking_pixel_id')->nullable()->after('tracking_enabled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropColumn(['tracking_enabled', 'tracking_pixel_id']);
        });
        
        Schema::dropIfExists('email_tracking');
    }
};