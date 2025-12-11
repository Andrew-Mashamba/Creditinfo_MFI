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
        Schema::table('domains', function (Blueprint $table) {
            // Add organization field for registrant
            $table->string('registrant_organization')->nullable()->after('registrant_name');
            
            // Add admin information fields
            $table->string('admin_name')->nullable()->after('registrant_organization');
            $table->string('admin_email')->nullable()->after('admin_name');
            $table->string('admin_phone')->nullable()->after('admin_email');
            
            // Add period field for registration period
            $table->integer('registration_period')->default(1)->after('admin_phone');
            
            // Add API response tracking fields
            $table->string('api_code')->nullable()->after('registration_period');
            $table->text('api_message')->nullable()->after('api_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn([
                'registrant_organization',
                'admin_name',
                'admin_email',
                'admin_phone',
                'registration_period',
                'api_code',
                'api_message'
            ]);
        });
    }
};
