<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('sub_products')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'product_name' => 'Sample Product',
                'product_type' => 4000,
                'product_id' => 1,
                'savings_type_id' => 1,
                'default_status' => 1,
                'sub_product_name' => 'BIASHARA LOANS',
                'sub_product_id' => 'SP001',
                'deposit_type_id' => 1,
                'share_type_id' => 1,
                'sub_product_status' => 1,
                'currency' => 'USD',
                'deposit' => 1,
                'deposit_charge' => 10,
                'min_balance' => 100,
                'created_by' => 1,
                'updated_by' => 1,
                'deposit_charge_min_value' => 5,
                'deposit_charge_max_value' => 20,
                'withdraw' => 1,
                'withdraw_charge' => 5,
                'withdraw_charge_min_value' => 2,
                'withdraw_charge_max_value' => 10,
                'interest_value' => 5,
                'interest_tenure' => 12,
                'maintenance_fees' => 15,
                'maintenance_fees_value' => 15,
                'profit_account' => 'Profit Account',
                'inactivity' => 'Active',
                'create_during_registration' => 1,
                'activated_by_lower_limit' => 1,
                'requires_approval' => 1,
                'generate_atm_card_profile' => 1,
                'allow_statement_generation' => 1,
                'send_notifications' => 1,
                'require_image_member' => 1,
                'require_image_id' => 1,
                'require_mobile_number' => 1,
                'generate_mobile_profile' => 1,
                'notes' => 'Sample notes for the sub product.',
                'interest' => 5,
                'ledger_fees' => 1,
                'ledger_fees_value' => 10,
                'total_shares' => 1000,
                'shares_per_member' => 10,
                'nominal_price' => 50,
                'shares_allocated' => 500,
                'available_shares' => 500,
                'branch' => 1,
                'category_code' => 1,
                'sub_category_code' => 1,
                'major_category_code' => 1,
                'status' => 'active',
                'collection_account_withdraw_charges' => 'Withdraw Charges Account',
                'collection_account_deposit_charges' => 'Deposit Charges Account',
                'collection_account_interest_charges' => 'Interest Charges Account',
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
                'product_account' => 'Product Account',
                'minimum_required_shares' => 100,
                'lock_in_period' => 30,
                'dividend_eligibility_period' => 90,
                'dividend_payment_frequency' => 'annual',
                'payment_methods' => null,
                'withdrawal_approval_level' => 1,
                'allow_share_transfer' => false,
                'allow_share_withdrawal' => false,
                'enable_dividend_calculation' => false,
                'sms_sender_name' => null,
                'sms_api_key' => null,
                'sms_enabled' => false,
                'issued_shares' => 0,
            ],
        ];

        foreach ($data as $row) {
            DB::table('sub_products')->insert($row);
    }
}
}