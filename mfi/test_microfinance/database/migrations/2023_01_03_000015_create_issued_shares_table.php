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
        Schema::create('issued_shares', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->nullable();
            $table->bigInteger('share_id')->nullable();
            $table->string('member')->nullable();
            $table->string('product')->nullable();
            $table->string('account_number')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->string('branch')->nullable();
            $table->string('client_number')->nullable();
            $table->integer('number_of_shares')->nullable();
            $table->decimal('nominal_price', 15, 2)->nullable();
            $table->decimal('total_value', 15, 2)->nullable();
            $table->string('linked_savings_account')->nullable();
            $table->string('linked_share_account')->nullable();
            $table->string('status')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Add indexes for the join query
            $table->index('client_number');
            $table->index('share_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issued_shares');
    }
};