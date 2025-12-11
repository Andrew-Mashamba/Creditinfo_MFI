<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PpesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement('SET session_replication_role = replica;');
        }
        
        try {
        // Clear existing data
        DB::table('ppes')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'name' => 'Sample name',
                'category' => 'Sample category 1',
                'purchase_price' => 1000,
                'purchase_date' => '2025-07-24',
                'salvage_value' => 1000.00,
                'quantity' => 10,
                'initial_value' => 1000.00,
                'depreciation_rate' => 5.5,
                'closing_value' => 1000.00,
                'status' => 'pending',
                'location' => 'Sample location 1',
                'notes' => 'Sample notes 1',
                'account_number' => 10,
                'depreciation_for_month' => 83.33,
                'created_at' => '2025-07-23 10:38:40',
                'updated_at' => now(),
                'legal_fees' => 500.00,
                'registration_fees' => 250.00,
                'renovation_costs' => 1500.00,
                'transportation_costs' => 300.00,
                'installation_costs' => 800.00,
                'other_costs' => 150.00,
                'payment_method' => 'cash',
                'payment_account_number' => 10,
                'payable_account_number' => 10,
                'accounting_transaction_id' => 1,
                'accounting_entry_created' => true,
                'supplier_name' => 'Sample supplier_name',
                'invoice_number' => 000001,
                'invoice_date' => '2025-07-24',
                'additional_notes' => 'Sample additional_notes 1',
                'disposal_date' => '2025-07-24',
                'disposal_method' => 'sold',
                'disposal_proceeds' => 900.00,
                'disposal_notes' => 'Sample disposal_notes 1',
                'disposal_approval_status' => 'pending',
                'disposal_approved_by' => 1,
                'disposal_approved_at' => '2025-07-24 10:00:00',
                'disposal_rejection_reason' => 'Sample disposal_rejection_reason 1',
            ],
            [
                'id' => 2,
                'name' => 'Sample name',
                'category' => 'Sample category 2',
                'purchase_price' => 2000,
                'purchase_date' => '2025-07-25',
                'salvage_value' => 1000.00,
                'quantity' => 20,
                'initial_value' => 1000.00,
                'depreciation_rate' => 11,
                'closing_value' => 1000.00,
                'status' => 'inactive',
                'location' => 'Sample location 2',
                'notes' => 'Sample notes 2',
                'account_number' => 20,
                'depreciation_for_month' => 166.67,
                'created_at' => '2025-07-23 11:38:40',
                'updated_at' => now(),
                'legal_fees' => 600.00,
                'registration_fees' => 300.00,
                'renovation_costs' => 2000.00,
                'transportation_costs' => 400.00,
                'installation_costs' => 1000.00,
                'other_costs' => 200.00,
                'payment_method' => 'credit',
                'payment_account_number' => 20,
                'payable_account_number' => 20,
                'accounting_transaction_id' => 2,
                'accounting_entry_created' => false,
                'supplier_name' => 'Sample supplier_name',
                'invoice_number' => 000002,
                'invoice_date' => '2025-07-25',
                'additional_notes' => 'Sample additional_notes 2',
                'disposal_date' => '2025-07-25',
                'disposal_method' => 'scrapped',
                'disposal_proceeds' => 1800.00,
                'disposal_notes' => 'Sample disposal_notes 2',
                'disposal_approval_status' => 'approved',
                'disposal_approved_by' => 1,
                'disposal_approved_at' => '2025-07-24 10:00:00',
                'disposal_rejection_reason' => 'Sample disposal_rejection_reason 2',
            ],
        ];

        foreach ($data as $row) {
            DB::table('ppes')->updateOrInsert(
                ['id' => $row['id']],
                $row
            );
        }
        
        } finally {
            // Re-enable foreign key checks
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } elseif (DB::getDriverName() === 'pgsql') {
                DB::statement('SET session_replication_role = DEFAULT;');
            }
        }
    }
}