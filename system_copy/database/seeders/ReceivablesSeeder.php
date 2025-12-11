<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReceivablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('receivables')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'account_number' => 10,
                'customer_id' => 1,
                'invoice_number' => 000001,
                'due_date' => '2025-07-24',
                'amount' => 1000,
                'status' => 'pending',
                'description' => 'This is a sample description for receivables record 1',
                'customer_name' => 'Sample customer_name',
                'source' => 'Sample source 1',
                'income_account' => 10,
                'asset_account' => 10,
                // 'created_at' => '2025-07-23 10:38:41',
                // 'updated_at' => now(),
                'receivable_type' => 'TYPE_B',
                'service_type' => 'TYPE_B',
                'property_type' => 'TYPE_B',
                'investment_type' => 'TYPE_B',
                'insurance_claim_type' => 'TYPE_B',
                'government_agency' => 'Sample government_agency 1',
                'contract_type' => 'TYPE_B',
                'subscription_type' => 'TYPE_B',
                'installment_plan' => 'Sample installment_plan 1',
                'royalty_type' => 'TYPE_B',
                'commission_type' => 'TYPE_B',
                'utility_type' => 'TYPE_B',
                'healthcare_type' => 'TYPE_B',
                'education_type' => 'TYPE_B',
                'aging_date' => '2025-07-24',
                'payment_terms' => 'Sample payment_terms 1',
                'collection_status' => 'PENDING',
                'collection_notes' => 'Sample collection_notes 1',
                'last_payment_date' => '2025-07-24',
                'last_payment_amount' => 1000,
                'payment_method' => 'Sample payment_method 1',
                'reference_number' => '000001',
                'revenue_category' => 'Sample revenue_category 1',
                'cost_center' => 'Sample cost_center 1',
                'project_code' => 'REC001',
                'department' => 'Sample department 1',
                'document_reference' => 'Sample document_reference 1',
                'approval_status' => 'pending',
                'approved_by' => 1,
                'approved_at' => now(),
            ],
            [
                'id' => 2,
                'account_number' => 20,
                'customer_id' => 2,
                'invoice_number' => 000002,
                'due_date' => '2025-07-25',
                'amount' => 2000,
                'status' => 'inactive',
                'description' => 'This is a sample description for receivables record 2',
                'customer_name' => 'Sample customer_name',
                'source' => 'Sample source 2',
                'income_account' => 20,
                'asset_account' => 20,
                // 'created_at' => '2025-07-23 11:38:41',
                // 'updated_at' => now(),
                'receivable_type' => 'TYPE_C',
                'service_type' => 'TYPE_C',
                'property_type' => 'TYPE_C',
                'investment_type' => 'TYPE_C',
                'insurance_claim_type' => 'TYPE_C',
                'government_agency' => 'Sample government_agency 2',
                'contract_type' => 'TYPE_C',
                'subscription_type' => 'TYPE_C',
                'installment_plan' => 'Sample installment_plan 2',
                'royalty_type' => 'TYPE_C',
                'commission_type' => 'TYPE_C',
                'utility_type' => 'TYPE_C',
                'healthcare_type' => 'TYPE_C',
                'education_type' => 'TYPE_C',
                'aging_date' => '2025-07-25',
                'payment_terms' => 'Sample payment_terms 2',
                'collection_status' => 'INACTIVE',
                'collection_notes' => 'Sample collection_notes 2',
                'last_payment_date' => '2025-07-25',
                'last_payment_amount' => 2000,
                'payment_method' => 'Sample payment_method 2',
                'reference_number' => '000002',
                'revenue_category' => 'Sample revenue_category 2',
                'cost_center' => 'Sample cost_center 2',
                'project_code' => 'REC002',
                'department' => 'Sample department 2',
                'document_reference' => 'Sample document_reference 2',
                'approval_status' => 'INACTIVE',
                'approved_by' => 1,
                'approved_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('receivables')->insert($row);
    }
}
}