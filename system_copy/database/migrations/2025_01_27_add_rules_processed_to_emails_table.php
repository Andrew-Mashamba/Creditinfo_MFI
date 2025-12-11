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
        Schema::table('emails', function (Blueprint $table) {
            $table->boolean('rules_processed')->default(false)->after('is_flagged');
            $table->boolean('is_important')->default(false)->after('rules_processed');
            $table->index(['recipient_id', 'rules_processed']);
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
            $table->dropIndex(['recipient_id', 'rules_processed']);
            $table->dropColumn(['rules_processed', 'is_important']);
        });
    }
};