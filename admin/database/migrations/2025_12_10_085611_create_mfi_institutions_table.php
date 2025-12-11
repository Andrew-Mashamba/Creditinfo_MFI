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
        Schema::create('mfi_institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('contact_person');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->text('address');
            $table->string('license_number')->nullable();
            $table->string('database_name');
            $table->string('folder_path');
            $table->string('admin_email');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->json('configuration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mfi_institutions');
    }
};
