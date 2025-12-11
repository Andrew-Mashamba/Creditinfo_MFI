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
            $table->boolean('request_read_receipt')->default(false)->after('track_clicks');
            $table->boolean('request_delivery_receipt')->default(false)->after('request_read_receipt');
            $table->timestamp('read_receipt_sent_at')->nullable()->after('request_delivery_receipt');
            $table->timestamp('delivery_receipt_sent_at')->nullable()->after('read_receipt_sent_at');
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
            $table->dropColumn(['request_read_receipt', 'request_delivery_receipt', 'read_receipt_sent_at', 'delivery_receipt_sent_at']);
        });
    }
};