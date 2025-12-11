<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApprovalsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('approvals')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'status' => 'pending',
                'created_at' => '2025-07-18 04:00:30',
                'updated_at' => '2025-07-18 06:43:26',
                'deleted_at' => null,
                'checker_level' => 3,
                'first_checker_id' => 1,
                'second_checker_id' => 1,
                'first_checker_status' => 'APPROVED',
                'second_checker_status' => 'APPROVED',
                'rejection_reason' => null,
                'approved_at' => null,
                'rejected_at' => null,
                'first_checked_at' => null,
                'second_checked_at' => null,
                'comments' => null,
                'last_action_by' => null,
                'process_name' => 'new_member_registration',
                'process_description' => 'Andrew S. Mashamba has requested to register a new member: JAFARI CHARLES',
                'approval_process_description' => 'New member registration approval required',
                'process_code' => 'MEMBER_REG',
                'process_id' => 4,
                'process_status' => 'approved',
                'approval_status' => 'approved',
                'user_id' => 1,
                'approver_id' => 1,
                'team_id' => 1,
                'edit_package' => null,
                'first_checker_rejection_reason' => null,
                'second_checker_rejection_reason' => null,
                'approver_rejection_reason' => null,
            ],
            [
                'id' => 2,
                'status' => 'pending',
                'created_at' => '2025-07-18 06:47:37',
                'updated_at' => '2025-07-18 06:49:39',
                'deleted_at' => null,
                'checker_level' => 3,
                'first_checker_id' => 1,
                'second_checker_id' => 1,
                'first_checker_status' => 'APPROVED',
                'second_checker_status' => 'APPROVED',
                'rejection_reason' => null,
                'approved_at' => null,
                'rejected_at' => null,
                'first_checked_at' => null,
                'second_checked_at' => null,
                'comments' => null,
                'last_action_by' => null,
                'process_name' => 'new_member_registration',
                'process_description' => 'Andrew S. Mashamba has requested to register a new member: JOHN  PENGO',
                'approval_process_description' => 'New member registration approval required',
                'process_code' => 'MEMBER_REG',
                'process_id' => 5,
                'process_status' => 'approved',
                'approval_status' => 'approved',
                'user_id' => 1,
                'approver_id' => 1,
                'team_id' => 1,
                'edit_package' => null,
                'first_checker_rejection_reason' => null,
                'second_checker_rejection_reason' => null,
                'approver_rejection_reason' => null,
            ],
            [
                'id' => 3,
                'status' => 'pending',
                'created_at' => '2025-07-18 07:02:58',
                'updated_at' => '2025-07-18 07:08:48',
                'deleted_at' => null,
                'checker_level' => 3,
                'first_checker_id' => 1,
                'second_checker_id' => 1,
                'first_checker_status' => 'APPROVED',
                'second_checker_status' => 'APPROVED',
                'rejection_reason' => null,
                'approved_at' => null,
                'rejected_at' => null,
                'first_checked_at' => null,
                'second_checked_at' => null,
                'comments' => null,
                'last_action_by' => null,
                'process_name' => 'Loan Approval',
                'process_description' => 'Loan approval request for client 10003 - Amount: 3,000,000.00 TZS',
                'approval_process_description' => 'Loan assessment completed and ready for approval',
                'process_code' => 'LOAN_DISB',
                'process_id' => 2,
                'process_status' => 'approved',
                'approval_status' => 'approved',
                'user_id' => 1,
                'approver_id' => 1,
                'team_id' => null,
                'edit_package' => null,
                'first_checker_rejection_reason' => null,
                'second_checker_rejection_reason' => null,
                'approver_rejection_reason' => null,
            ],
            [
                'id' => 4,
                'status' => 'pending',
                'created_at' => '2025-07-22 08:43:51',
                'updated_at' => '2025-07-22 08:43:51',
                'deleted_at' => null,
                'checker_level' => null,
                'first_checker_id' => null,
                'second_checker_id' => null,
                'first_checker_status' => null,
                'second_checker_status' => null,
                'rejection_reason' => null,
                'approved_at' => null,
                'rejected_at' => null,
                'first_checked_at' => null,
                'second_checked_at' => null,
                'comments' => null,
                'last_action_by' => null,
                'process_name' => 'new_expense_request',
                'process_description' => 'Andrew S. Mashamba has registered an expense: hghghgh',
                'approval_process_description' => 'Expense approval required',
                'process_code' => 'EXPENSE_REG',
                'process_id' => 2,
                'process_status' => 'pending',
                'approval_status' => 'pending',
                'user_id' => 1,
                'approver_id' => null,
                'team_id' => null,
                'edit_package' => null,
                'first_checker_rejection_reason' => null,
                'second_checker_rejection_reason' => null,
                'approver_rejection_reason' => null,
            ],
        ];

        foreach ($data as $row) {
            // Remove the hardcoded ID to let the database auto-increment
            unset($row['id']);
            
            // Validate user references
            $userFields = ['first_checker_id', 'second_checker_id', 'user_id', 'approver_id', 'last_action_by'];
            foreach ($userFields as $field) {
                if (isset($row[$field]) && $row[$field]) {
                    $userExists = DB::table('users')->where('id', $row[$field])->exists();
                    if (!$userExists) {
                        // Use first user or null
                        $firstUser = DB::table('users')->first();
                        $row[$field] = $firstUser ? $firstUser->id : null;
                    }
                }
            }
            
            // Validate team_id - set to null since teams table doesn't exist
            if (isset($row['team_id'])) {
                $row['team_id'] = null;
            }
            
            DB::table('approvals')->insert($row);
    }
}
}