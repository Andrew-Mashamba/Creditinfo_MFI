<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Increases loan_status column size to accommodate longer status values
     * such as "PENDING-WITH-EXCEPTIONS" (24 chars)
     *
     * @return void
     */
    public function up()
    {
        // SAFE: Only increases column size, does NOT delete or truncate data
        Schema::table('loans', function (Blueprint $table) {
            $table->string('loan_status', 50)->default('NORMAL')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * WARNING: Rollback will fail if any loan_status values exceed 20 characters
     *
     * @return void
     */
    public function down()
    {
        // Rollback to original size (only if no data exceeds 20 chars)
        Schema::table('loans', function (Blueprint $table) {
            $table->string('loan_status', 20)->default('NORMAL')->change();
        });
    }
};
