<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HiresapprovalsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('hires_approvals')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'user_id' => 1,
                'user_name' => 'Sample user_name',
                'status' => 'pending',
                'employee_id' => 1,
                'created_at' => '2025-07-23 10:38:36',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'user_name' => 'Sample user_name',
                'status' => 'inactive',
                'employee_id' => 2,
                'created_at' => '2025-07-23 11:38:36',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            // Validate user_id and employee_id (both required)
            $userExists = DB::table('users')->where('id', $row['user_id'])->exists();
            if (!$userExists) {
                $firstUser = DB::table('users')->first();
                if (!$firstUser) {
                    if ($this->command) $this->command->warn("Skipping hires_approval - no users found");
                    continue;
                }
                $row['user_id'] = $firstUser->id;
            }
            
            // Check employee_id (required field)
            $employeeExists = DB::table('employees')->where('id', $row['employee_id'])->exists();
            if (!$employeeExists) {
                // Try to find any employee or use user_id as fallback
                $firstEmployee = DB::table('employees')->first();
                if ($firstEmployee) {
                    $row['employee_id'] = $firstEmployee->id;
                } else {
                    // Use user_id as employee_id since it's required
                    $row['employee_id'] = $row['user_id'];
                }
            }
            
            DB::table('hires_approvals')->insert($row);
    }
}
}