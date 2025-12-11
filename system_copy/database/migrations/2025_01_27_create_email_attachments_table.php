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
        Schema::create('email_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('email_id');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->bigInteger('size'); // in bytes
            $table->string('path'); // storage path
            $table->string('disk')->default('local'); // storage disk
            $table->string('checksum')->nullable(); // for integrity check
            $table->boolean('is_inline')->default(false); // for inline images
            $table->string('content_id')->nullable(); // for inline images
            $table->json('metadata')->nullable(); // additional metadata
            $table->timestamps();
            
            $table->foreign('email_id')->references('id')->on('emails')->onDelete('cascade');
            $table->index(['email_id', 'created_at']);
            $table->index('checksum');
        });
        
        // Add has_attachments column to emails if it doesn't exist
        if (!Schema::hasColumn('emails', 'has_attachments')) {
            Schema::table('emails', function (Blueprint $table) {
                $table->boolean('has_attachments')->default(false)->after('body');
                $table->integer('attachment_count')->default(0)->after('has_attachments');
                $table->bigInteger('total_attachment_size')->default(0)->after('attachment_count');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_attachments');
        
        if (Schema::hasColumn('emails', 'has_attachments')) {
            Schema::table('emails', function (Blueprint $table) {
                $table->dropColumn(['has_attachments', 'attachment_count', 'total_attachment_size']);
            });
        }
    }
};