<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('scheduled_reports', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('scheduled_reports', 'report_config')) {
                $table->json('report_config')->after('report_type');
            }
            
            if (!Schema::hasColumn('scheduled_reports', 'frequency')) {
                $table->enum('frequency', ['once', 'daily', 'weekly', 'monthly', 'quarterly', 'annually'])->default('once')->after('status');
            }
            
            if (!Schema::hasColumn('scheduled_reports', 'file_path')) {
                $table->string('file_path')->nullable()->after('generated_at');
            }
            
            if (!Schema::hasColumn('scheduled_reports', 'email_recipients')) {
                $table->json('email_recipients')->nullable()->after('file_path');
            }
            
            if (!Schema::hasColumn('scheduled_reports', 'email_subject')) {
                $table->string('email_subject')->nullable()->after('email_recipients');
            }
            
            if (!Schema::hasColumn('scheduled_reports', 'email_message')) {
                $table->text('email_message')->nullable()->after('email_subject');
            }
            
            if (!Schema::hasColumn('scheduled_reports', 'error_message')) {
                $table->text('error_message')->nullable()->after('email_message');
            }
        });
    }

    public function down()
    {
        Schema::table('scheduled_reports', function (Blueprint $table) {
            $table->dropColumn([
                'report_config',
                'frequency',
                'file_path',
                'email_recipients',
                'email_subject',
                'email_message',
                'error_message'
            ]);
        });
    }
};
