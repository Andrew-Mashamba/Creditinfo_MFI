<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplaintCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('complaint_categories')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'name' => 'Account Issues',
                'description' => 'Complaints related to account management, access, or account-related problems',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                'id' => 2,
                'name' => 'Loan Services',
                'description' => 'Complaints related to loan applications, disbursements, or loan account management',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                'id' => 3,
                'name' => 'Savings & Deposits',
                'description' => 'Complaints related to savings accounts, deposits, or withdrawal issues',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                'id' => 4,
                'name' => 'Customer Service',
                'description' => 'Complaints about staff behavior, service quality, or general customer service issues',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                'id' => 5,
                'name' => 'Technical Issues',
                'description' => 'Complaints related to system downtime, online banking, or technical problems',
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                'id' => 6,
                'name' => 'Fees & Charges',
                'description' => 'Complaints about unexpected fees, charges, or billing issues',
                'is_active' => true,
                'sort_order' => 6,
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                'id' => 7,
                'name' => 'Security Concerns',
                'description' => 'Complaints related to account security, fraud, or suspicious activities',
                'is_active' => true,
                'sort_order' => 7,
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                'id' => 8,
                'name' => 'Other',
                'description' => 'General complaints that do not fit into other categories',
                'is_active' => true,
                'sort_order' => 8,
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
        ];

        foreach ($data as $row) {
            DB::table('complaint_categories')->insert($row);
    }
}
}