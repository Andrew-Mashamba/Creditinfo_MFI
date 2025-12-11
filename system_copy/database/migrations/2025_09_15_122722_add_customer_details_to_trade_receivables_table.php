<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trade_receivables', function (Blueprint $table) {
            $table->string('customer_company')->nullable()->after('customer_name');
            $table->string('customer_email')->nullable()->after('customer_company');
            $table->string('customer_phone')->nullable()->after('customer_email');
            $table->text('customer_address')->nullable()->after('customer_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_receivables', function (Blueprint $table) {
            $table->dropColumn(['customer_company', 'customer_email', 'customer_phone', 'customer_address']);
        });
    }
};