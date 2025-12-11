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
        Schema::create('share_transfers', function (Blueprint $table) {
            $table->id();
            
       
            // Transaction Reference
            $table->string('transaction_reference')->unique();
            
            // Sender Information
            $table->unsignedBigInteger('sender_member_id')->nullable();
            $table->string('sender_client_number', 50)->nullable();
            $table->string('sender_member_name', 255)->nullable();
            $table->unsignedBigInteger('sender_share_register_id')->nullable();
            $table->string('sender_share_account_number', 50)->nullable();
            
            // Receiver Information
            $table->unsignedBigInteger('receiver_member_id')->nullable();
            $table->string('receiver_client_number', 50)->nullable();
            $table->string('receiver_member_name', 255)->nullable();
            $table->unsignedBigInteger('receiver_share_register_id')->nullable();
            $table->string('receiver_share_account_number', 50)->nullable();
            
            // Share Details
            $table->unsignedBigInteger('share_product_id')->nullable();
            $table->string('share_product_name', 100)->nullable();
            $table->integer('number_of_shares')->default(0);
            $table->decimal('nominal_price', 20, 6)->nullable();
            $table->decimal('total_value', 20, 6)->nullable();
            
            // Transfer Details
            $table->text('transfer_reason')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'COMPLETED'])->default('PENDING');
            $table->text('rejection_reason')->nullable();
            
 
            
            // Audit Fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for Performance
            $table->index(['sender_client_number', 'status']);
            $table->index(['receiver_client_number', 'status']);
            $table->index('transaction_reference');
            $table->index('created_at');
            $table->index(['share_product_id', 'status']);
            
            // Foreign Keys
            // $table->foreign('institution_id')->references('id')->on('institutions');
            // $table->foreign('sender_member_id')->references('id')->on('members');
            // $table->foreign('receiver_member_id')->references('id')->on('members');
            // $table->foreign('share_product_id')->references('id')->on('sub_products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_transfers');
    }
}; 