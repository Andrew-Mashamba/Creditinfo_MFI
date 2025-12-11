<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('collaterals', function (Blueprint $table) {
            $table->id();
            $table->string('loan_id', 255)->notNullable();
            $table->string('main_collateral_type', 240)->notNullable();
            $table->string('collateral_value', 255)->notNullable();
            $table->string('member_number', 255)->notNullable();
            $table->string('account_id', 255)->notNullable();
            $table->string('collateral_category', 255)->notNullable();
            $table->string('collateral_type', 255)->notNullable();
            $table->text('description')->notNullable();
            $table->string('collateral_id', 255)->notNullable();
            $table->string('client_id', 255)->notNullable();
            $table->string('type_of_owner', 255)->nullable();
            $table->string('relationship', 255)->nullable();
            $table->string('collateral_owner_full_name', 255)->nullable();
            $table->string('collateral_owner_nida', 255)->nullable();
            $table->string('collateral_owner_contact_number', 255)->nullable();
            $table->text('collateral_owner_residential_address')->nullable();
            $table->string('collateral_owner_spouse_full_name', 255)->nullable();
            $table->string('collateral_owner_spouse_nida', 255)->nullable();
            $table->string('collateral_owner_spouse_contact_number', 255)->nullable();
            $table->text('collateral_owner_spouse_residential_address')->nullable();
            $table->string('company_registered_name', 255)->nullable();
            $table->string('business_licence_number', 255)->nullable();
            $table->string('tin', 255)->nullable();
            $table->string('director_nida', 255)->nullable();
            $table->string('director_contact', 255)->nullable();
            $table->text('director_address')->nullable();
            $table->text('business_address')->nullable();
            $table->date('date_of_valuation')->nullable();
            $table->string('valuation_method_used', 255)->nullable();
            $table->string('name_of_valuer', 255)->nullable();
            $table->string('policy_number', 255)->nullable();
            $table->string('company_name', 255)->nullable();
            $table->text('coverage_details')->nullable();
            $table->date('expiration_date')->nullable();
            $table->date('disbursement_date')->nullable();
            $table->integer('tenure')->nullable();
            $table->integer('interest')->nullable();
            $table->decimal('loan_amount', 10, 2)->nullable();
            $table->string('physical_condition', 255)->nullable();
            $table->string('current_status', 255)->nullable();
            $table->string('region', 255)->nullable();
            $table->string('district', 255)->nullable();
            $table->string('ward', 255)->nullable();
            $table->string('postal_code', 255)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('building_number', 220)->nullable();
            $table->string('release_status', 50)->default('held');
            $table->string('collateral_file', 300)->nullable();
            $table->string('collateral_file_name', 300)->nullable();
            $table->boolean('collateral_file_rejected')->nullable();
            $table->boolean('expiration_period')->nullable();
            $table->boolean('expiration_period_rejected')->nullable();
            $table->string('approval_status', 150)->nullable();
            $table->string('guarantor_id', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('collaterals');
    }
}; 