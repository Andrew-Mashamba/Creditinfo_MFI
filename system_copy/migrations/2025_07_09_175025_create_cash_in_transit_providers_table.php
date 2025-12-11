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
        Schema::create('cash_in_transit_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_code')->unique();
            $table->string('contact_person')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('license_number')->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->decimal('service_fee_percentage', 5, 2)->default(0)->comment('Percentage fee charged by CIT provider');
            $table->decimal('minimum_fee', 10, 2)->default(0)->comment('Minimum fee amount');
            $table->json('service_areas')->nullable()->comment('Areas/regions they serve');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'name']);
            $table->index('company_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_in_transit_providers');
    }
};
