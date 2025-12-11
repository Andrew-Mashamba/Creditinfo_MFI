<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplaintStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('complaint_statuses')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'name' => 'Pending',
                'description' => 'Complaint has been submitted and is awaiting review',
                'color' => '#F59E0B',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                'id' => 2,
                'name' => 'In Progress',
                'description' => 'Complaint is being investigated and worked on',
                'color' => '#3B82F6',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                'id' => 3,
                'name' => 'Resolved',
                'description' => 'Complaint has been resolved and closed',
                'color' => '#10B981',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                'id' => 4,
                'name' => 'Closed',
                'description' => 'Complaint has been closed without resolution',
                'color' => '#6B7280',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                'id' => 5,
                'name' => 'Escalated',
                'description' => 'Complaint has been escalated to higher management',
                'color' => '#EF4444',
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
        ];

        foreach ($data as $row) {
            DB::table('complaint_statuses')->insert($row);
    }
}
}