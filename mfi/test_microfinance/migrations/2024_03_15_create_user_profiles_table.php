<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('employee_id')->unique();
            $table->string('job_title');
            $table->string('phone_number');
            $table->json('emergency_contact');
            $table->date('date_of_birth');
            $table->date('hire_date');
            $table->string('employment_status');
            $table->string('employment_type');
            $table->string('salary_grade');
            $table->foreignId('reporting_manager_id')->nullable()->constrained('users');
            $table->json('skills')->nullable();
            $table->json('certifications')->nullable();
            $table->json('education')->nullable();
            $table->json('work_experience')->nullable();
            $table->json('preferences')->nullable();
            $table->string('language_preference')->default('en');
            $table->string('timezone')->default('UTC');
            $table->json('notification_preferences')->nullable();
            $table->integer('profile_completion_percentage')->default(0);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_profiles');
    }
}; 