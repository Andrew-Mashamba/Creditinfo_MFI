<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('role_menu_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('cascade');
            $table->string('sub_role')->nullable();
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->json('allowed_actions');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_menu_actions');
    }
}; 