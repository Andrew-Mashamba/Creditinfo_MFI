<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('onboarding', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('job_posting_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('start_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            
            // Essential Documents
            $table->string('cv_path')->nullable();
            $table->string('national_id_path')->nullable();
            $table->string('passport_photo_path')->nullable();
            $table->string('employment_contract_path')->nullable();
            $table->string('bank_account_details_path')->nullable();
            
            // Basic Information
            $table->string('full_name');
            $table->date('date_of_birth');
            $table->string('nationality');
            $table->string('nida_number')->unique();
            $table->string('tin_number')->unique();
            $table->string('physical_address');
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');
            
            // System Setup
            $table->string('workstation_id')->nullable();
            $table->boolean('email_created')->default(false);
            $table->boolean('system_access')->default(false);
            $table->boolean('id_badge')->default(false);
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('onboarding');
    }
}; 