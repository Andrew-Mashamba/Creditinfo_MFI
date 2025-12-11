<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sub_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_role_id')->constrained('sub_roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->json('conditions')->nullable(); // Additional conditions for the permission
            $table->boolean('is_inherited')->default(false); // Whether this permission is inherited from a parent role
            $table->timestamps();

            $table->unique(['sub_role_id', 'permission_id', 'department_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sub_role_permissions');
    }
}; 