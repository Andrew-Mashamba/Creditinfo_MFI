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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain_name')->unique();
            $table->string('registrant_name');
            $table->string('registrant_email');
            $table->string('registrant_phone');
            $table->text('registrant_address');
            $table->string('city');
            $table->string('country', 2);
            $table->json('nameservers');
            $table->date('registration_date');
            $table->date('expiry_date');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['active', 'expired', 'suspended', 'pending'])->default('pending');
            $table->timestamps();
            
            $table->index(['status', 'expiry_date']);
            $table->index('domain_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('domains');
    }
};
