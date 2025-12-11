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
        Schema::create('member_exits', function (Blueprint $table) {
            $table->id();
            $table->string('client_number');
            $table->string('member_name');
            $table->date('exit_date');
            $table->string('exit_reason');
            $table->text('exit_notes')->nullable();
            $table->decimal('shares_balance', 15, 2)->default(0);
            $table->decimal('savings_balance', 15, 2)->default(0);
            $table->decimal('settlement_amount', 15, 2)->default(0);
            $table->unsignedBigInteger('processed_by');
            $table->timestamps();
            
            $table->index('client_number');
            $table->index('exit_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_exits');
    }
};