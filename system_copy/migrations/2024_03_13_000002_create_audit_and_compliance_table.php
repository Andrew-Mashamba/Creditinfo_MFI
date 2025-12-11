<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('AUDIT_AND_COMPLIANCE', function (Blueprint $table) {
            $table->id();
            $table->string('category_code')->nullable();
            $table->string('sub_category_code')->nullable();
            $table->string('sub_category_name')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('AUDIT_AND_COMPLIANCE');
    }
}; 