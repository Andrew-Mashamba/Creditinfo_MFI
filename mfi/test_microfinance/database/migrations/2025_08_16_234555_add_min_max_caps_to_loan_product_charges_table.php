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
        Schema::table('loan_product_charges', function (Blueprint $table) {
            $table->decimal('min_cap', 10, 2)->nullable()->after('value');
            $table->decimal('max_cap', 10, 2)->nullable()->after('min_cap');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_product_charges', function (Blueprint $table) {
            $table->dropColumn(['min_cap', 'max_cap']);
        });
    }
};
