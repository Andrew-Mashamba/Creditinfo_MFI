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
        Schema::create('share_redemptions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 50)->unique();
            $table->unsignedBigInteger('share_id');
            $table->string('member', 255)->nullable();
            $table->unsignedBigInteger('product');
            $table->string('account_number', 50)->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->string('branch', 50)->nullable();
            $table->string('client_number', 10);
            $table->integer('number_of_shares')->default(0);
            $table->decimal('nominal_price', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->string('linked_savings_account', 50)->nullable();
            $table->string('linked_share_account', 50)->nullable();
            $table->string('transaction_type', 50)->default('REDEMPTION');
            $table->string('status', 20)->default('PENDING');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('reference_number');
            $table->index('client_number');
            $table->index('status');
            $table->index('product');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('share_redemptions');
    }
};
